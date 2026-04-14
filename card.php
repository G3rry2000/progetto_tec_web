<?php
session_start();
require_once 'includes/db_connect.php';

// SICUREZZA: Accesso riservato ai membri loggati
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

// LOGICA DI RECUPERO: Calcoliamo il Power Index direttamente nella query SQL
$query = "SELECT *, 
          (presenze * 2 + gol * 10 - ammonizioni * 2 - espulsioni * 5) as power_index 
          FROM giocatori 
          ORDER BY ruolo DESC, nome ASC";
$resG = pg_query($db, $query);

include 'includes/header.php';
?>

<link rel="stylesheet" href="css/styleCard.css?v=<?php echo time(); ?>">

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
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>