<?php
session_start();
require_once 'includes/db_connect.php'; // Usa la connessione $db
include 'includes/header.php';
?>

<link rel="stylesheet" href="css/style3.css">

<div class="stagione-hero">
    <p class="stagione-label">Terza Categoria · Avellino · Girone B</p>
    <h1>Stagione 2024<span>/2025</span></h1>
</div>

<nav class="stagione-tabs" role="tablist">
    <button class="stab-btn active" onclick="apriTab('classifica',this)" role="tab">🏅 Classifica</button>
    <button class="stab-btn" onclick="apriTab('calendario',this)" role="tab">📅 Calendario</button>
    <button class="stab-btn" onclick="apriTab('rosa',this)" role="tab">👥 Rosa</button>
</nav>

<section id="tab-classifica" class="stab-panel active" role="tabpanel">
    <h2 class="stagione-section-title">Classifica Girone B</h2>
    <table class="classifica-table">
        <thead>
            <tr>
                <th class="col-pos">#</th>
                <th class="col-nome">Squadra</th>
                <th>PT</th><th>G</th><th>V</th><th>N</th><th>P</th><th>DR</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $res = pg_query($db, "SELECT * FROM classifica ORDER BY pt DESC, dr DESC");
        $pos = 1;
        while ($t = pg_fetch_assoc($res)):
            $rowClass = ($t['is_morra'] == 't') ? 'riga-morra' : ($pos <= 2 ? 'riga-promo' : '');
        ?>
            <tr class="<?= $rowClass ?>">
                <td class="col-pos"><?= $pos++ ?></td>
                <td class="col-nome <?= ($t['is_morra'] == 't') ? 'nome-morra' : '' ?>">
                    <?= htmlspecialchars($t['squadra']) ?>
                </td>
                <td class="col-pt"><?= $t['pt'] ?></td>
                <td><?= $t['g'] ?></td><td><?= $t['v'] ?></td><td><?= $t['n'] ?></td><td><?= $t['p'] ?></td>
                <td><?= $t['dr'] > 0 ? '+' . $t['dr'] : $t['dr'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</section>

<section id="tab-calendario" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Calendario</h2>
    <div class="calendario-lista">
        <?php
        $resPartite = pg_query($db, "SELECT * FROM public.partite ORDER BY giornata ASC");
        while ($p = pg_fetch_assoc($resPartite)):
            $isMorraCasa = (strpos($p['casa'], 'Morra') !== false);
            $mClass = 'match-future';
            $esito = '–';
            $eClass = 'esito-future';

            if ($p['giocata'] == 't') {
                $golMorra = $isMorraCasa ? $p['gol_casa'] : $p['gol_ospite'];
                $golAvv = $isMorraCasa ? $p['gol_ospite'] : $p['gol_casa'];
                
                if ($golMorra > $golAvv) { $mClass = 'match-win'; $esito = 'V'; $eClass = 'esito-v'; }
                elseif ($golMorra < $golAvv) { $mClass = 'match-lose'; $esito = 'S'; $eClass = 'esito-s'; }
                else { $mClass = 'match-draw'; $esito = 'P'; $eClass = 'esito-p'; }
            }
        ?>
        <div class="match-row <?= $mClass ?>">
            <span class="match-giornata">G<?= $p['giornata'] ?></span>
            <span class="match-team <?= $isMorraCasa ? 'team-morra' : '' ?>"><?= htmlspecialchars($p['casa']) ?></span>
            
            <?php if ($p['giocata'] == 't'): ?>
                <span class="match-score <?= ($esito == 'V') ? 'score-win' : (($esito == 'S') ? 'score-lose' : 'score-draw') ?>">
                    <?= $p['gol_casa'] ?> – <?= $p['gol_ospite'] ?>
                </span>
            <?php else: ?>
                <span class="match-score score-future"><?= $p['data_match'] ?></span>
            <?php endif; ?>
            
            <span class="match-team team-right <?= !$isMorraCasa ? 'team-morra' : '' ?>"><?= htmlspecialchars($p['ospite']) ?></span>
            <span class="match-esito <?= $eClass ?>"><?= $esito ?></span>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<section id="tab-rosa" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Rosa Ufficiale</h2>
    <div class="rosa-grid">
    <?php
    $resG = pg_query($db, "SELECT * FROM giocatori ORDER BY ruolo DESC, nome ASC");
    $giocatori = pg_fetch_all($resG);

    if ($giocatori):
        foreach ($giocatori as $g):
            $rk = strtoupper($g['ruolo']);
    ?>
        <article class="giocatore-card">
            <span class="ruolo-badge badge-<?= strtolower($rk) ?>"><?= $rk ?></span>
            <h3 class="card-nome"><?= htmlspecialchars($g['nome']) ?></h3>
            
            <div class="card-stats <?= !isset($_SESSION['id_utente']) ? 'card-stats-blur' : '' ?>">
                <div class="stat-box"><span class="stat-num"><?= $g['gol'] ?></span><span class="stat-lbl">Gol</span></div>
                <div class="stat-box"><span class="stat-num"><?= $g['presenze'] ?></span><span class="stat-lbl">Pres.</span></div>
                <div class="stat-box"><span class="stat-num"><?= $g['ammonizioni'] ?></span><span class="stat-lbl">🟨</span></div>
            </div>
            <?php if(!isset($_SESSION['id_utente'])): ?>
                <p class="card-login-msg"><a href="login.php">Accedi</a> per i dettagli</p>
            <?php endif; ?>
        </article>
    <?php endforeach; else: ?>
        <p style='color:white;'>Nessun giocatore trovato nel database.</p>
    <?php endif; ?>
    </div>
</section>

<script>
// Funzione per il cambio Tab
function apriTab(nome, btn) {
    document.querySelectorAll('.stab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.stab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + nome).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>