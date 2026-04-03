<?php
session_start();
require_once 'includes/db_connect.php';

// SICUREZZA: Se l'utente non è loggato, va al login
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

// RECUPERO GIOCATORI: Calcoliamo il Power Index direttamente nella query
$query = "SELECT *, 
          (presenze * 2 + gol * 10 - ammonizioni * 2 - espulsioni * 5) as power_index 
          FROM giocatori 
          ORDER BY ruolo DESC, nome ASC";
$resG = pg_query($db, $query);

include 'includes/header.php';
?>

<style>
    :root {
        --gold-card: linear-gradient(135deg, #d4af37 0%, #fcf6ba 50%, #d4af37 100%);
        --red-card: linear-gradient(135deg, #8b0000 0%, #ff4d4d 50%, #8b0000 100%); /* Effetto Rosso */
        --dark-glass: rgba(22, 27, 34, 0.8);
        --morra-green: #2e7d32;
        --morra-red: #ff4444;
    }

    .almanacco-section {
        padding: 40px 20px;
        background: #0d1117;
        min-height: 100vh;
    }

    .page-title {
        text-align: center;
        margin-bottom: 40px;
    }
    .page-title h1 { font-size: 3rem; font-weight: 900; color: white; letter-spacing: -1px; }
    .page-title p { color: #8b949e; font-size: 1.1rem; }

    /* Filtri Ruolo */
    .filter-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 50px;
        flex-wrap: wrap;
    }
    .f-btn {
        background: #21262d;
        border: 1px solid #30363d;
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: 600;
    }
    .f-btn.active, .f-btn:hover {
        background: var(--morra-green);
        border-color: #4caf50;
        box-shadow: 0 0 15px rgba(76, 175, 80, 0.4);
    }

    /* Grid delle Card */
    .almanacco-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Stile Card Base */
    .p-card {
        background: #161b22;
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        border: 1px solid #30363d;
        position: relative;
        transition: all 0.4s ease;
        overflow: hidden;
    }
    .p-card:hover {
        transform: translateY(-10px) rotateX(5deg);
        border-color: #58a6ff;
        box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }

    /* --- EFFETTI SPECIALI (GOLD & RED) --- */
    
    /* Card Gold */
    .p-card.legendary { border: 2px solid #d4af37; background: linear-gradient(160deg, #161b22 0%, #1c2128 100%); }
    .p-card.legendary::after { background: linear-gradient(45deg, transparent, rgba(255,215,0,0.1), transparent); }

    /* Card Red (Valori Negativi) */
    .p-card.negative { 
        border: 2px solid var(--morra-red); 
        background: linear-gradient(160deg, #1a0505 0%, #161b22 100%); 
        box-shadow: inset 0 0 20px rgba(255, 0, 0, 0.1);
    }
    .p-card.negative::after { background: linear-gradient(45deg, transparent, rgba(255, 0, 0, 0.2), transparent); }

    /* Animazione Shine Comune */
    .p-card.legendary::after, .p-card.negative::after {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        transform: rotate(45deg);
        animation: shine 4s infinite;
        pointer-events: none;
    }
    @keyframes shine { 0% { left: -100%; } 100% { left: 100%; } }

    /* Colori Ruoli e Avatar */
    .p-ruolo { position: absolute; top: 15px; left: 20px; font-size: 1.2rem; font-weight: 900; color: var(--morra-green); }
    .legendary .p-ruolo { color: #d4af37; }
    .negative .p-ruolo { color: var(--morra-red); }

    .p-avatar {
        width: 110px; height: 110px; background: #30363d; border-radius: 50%;
        margin: 10px auto 20px; display: flex; align-items: center; justify-content: center;
        font-size: 3rem; color: #8b949e; border: 4px solid var(--morra-green);
    }
    .legendary .p-avatar { border-color: #d4af37; color: #d4af37; }
    .negative .p-avatar { border-color: var(--morra-red); color: var(--morra-red); }

    .p-name { font-size: 1.3rem; font-weight: 700; color: white; margin-bottom: 20px; }

    /* Statistiche */
    .p-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-top: 1px solid #30363d; padding-top: 20px; }
    .s-val { font-size: 1.5rem; font-weight: 800; color: white; }
    .s-lbl { font-size: 0.7rem; color: #8b949e; text-transform: uppercase; }

    .p-index { margin-top: 20px; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px; }
    .p-index .val { color: #4caf50; font-weight: 900; font-size: 1.4rem; }
    
    /* Colori dinamici Index */
    .legendary .p-index .val { color: #d4af37; }
    .negative .p-index .val { color: var(--morra-red); text-shadow: 0 0 10px rgba(255, 68, 68, 0.4); }

    .p-badges { margin-top: 15px; display: flex; justify-content: center; gap: 10px; }
    .badge { font-size: 0.8rem; padding: 4px 8px; border-radius: 5px; background: #21262d; }
</style>

<div class="almanacco-section">
    <div class="page-title">
        <h1>L'ALMANACCO DEI LUPI</h1>
        <p>Statistiche ufficiali e record della rosa ASD Morra De Sanctis</p>
    </div>

    <div class="filter-container">
        <button class="f-btn active" onclick="filterPlayers('all', this)">TUTTI</button>
        <button class="f-btn" onclick="filterPlayers('POR', this)">PORTIERI</button>
        <button class="f-btn" onclick="filterPlayers('DIF', this)">DIFENSORI</button>
        <button class="f-btn" onclick="filterPlayers('CEN', this)">CENTROCAMPISTI</button>
        <button class="f-btn" onclick="filterPlayers('ATT', this)">ATTACCANTI</button>
    </div>

    <div class="almanacco-grid" id="player-grid">
        <?php while($g = pg_fetch_assoc($resG)): 
            $valoreIndex = (int)$g['power_index'];
            
            // Logica Classi: Se negativo vince il rosso, se >= 3 gol vince l'oro
            $specialClass = '';
            if ($valoreIndex < 0) {
                $specialClass = 'negative';
            } elseif ($g['gol'] >= 3) {
                $specialClass = 'legendary';
            }
        ?>
            <div class="p-card <?= $specialClass ?>" data-role="<?= $g['ruolo'] ?>">
                <div class="p-ruolo"><?= $g['ruolo'] ?></div>
                
                <div class="p-avatar">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <div class="p-name"><?= htmlspecialchars($g['nome']) ?></div>

                <div class="p-stats">
                    <div class="s-box">
                        <span class="s-val"><?= $g['presenze'] ?></span>
                        <span class="s-lbl">Presenze</span>
                    </div>
                    <div class="s-box">
                        <span class="s-val"><?= $g['gol'] ?></span>
                        <span class="s-lbl">Goal</span>
                    </div>
                </div>

                <div class="p-index">
                    <span class="s-lbl">Wolves Index</span>
                    <span class="val"><?= $valoreIndex ?></span>
                </div>

                <div class="p-badges">
                    <span class="badge" title="Gialli">🟨 <?= $g['ammonizioni'] ?></span>
                    <span class="badge" title="Rossi">🟥 <?= $g['espulsioni'] ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function filterPlayers(role, btn) {
    document.querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.p-card');
    cards.forEach(card => {
        if(role === 'all' || card.dataset.role === role) {
            card.style.display = 'block';
            card.style.animation = 'fadeIn 0.5s ease forwards';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>