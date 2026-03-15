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
        $classifica = [
            ['nome'=>'Castelfranci', 'pt'=>29,'g'=>11,'v'=>9,'n'=>2,'p'=>0,'dr'=>26],
            ['nome'=>'FC Montemarano', 'pt'=>26,'g'=>11,'v'=>8,'n'=>2,'p'=>1,'dr'=>22],
            ['nome'=>'Teora', 'pt'=>24,'g'=>11,'v'=>7,'n'=>3,'p'=>1,'dr'=>25],
            ['nome'=>"Nusco '75", 'pt'=>22,'g'=>11,'v'=>7,'n'=>1,'p'=>3,'dr'=>13],
            ['nome'=>'Morra De Sanctis', 'pt'=>17,'g'=>11,'v'=>5,'n'=>2,'p'=>4,'dr'=>1, 'morra'=>true],
            ['nome'=>'Andretta', 'pt'=>16,'g'=>11,'v'=>4,'n'=>4,'p'=>3,'dr'=>2],
            ['nome'=>'Villamaina', 'pt'=>3, 'g'=>11,'v'=>0,'n'=>3,'p'=>8,'dr'=>-26]
        ];
       
        foreach ($classifica as $i => $t):
            $pos = $i + 1;
            $rowClass = isset($t['morra']) ? 'riga-morra' : ($pos <= 2 ? 'riga-promo' : '');
        ?>
        <tr class="<?= $rowClass ?>">
            <td class="col-pos"><?= $pos ?></td>
            <td class="col-nome <?= isset($t['morra']) ? 'nome-morra' : '' ?>"><?= htmlspecialchars($t['nome']) ?></td>
            <td class="col-pt"><?= $t['pt'] ?></td>
            <td><?= $t['g'] ?></td><td><?= $t['v'] ?></td><td><?= $t['n'] ?></td><td><?= $t['p'] ?></td>
            <td><?= $t['dr'] > 0 ? '+'.$t['dr'] : $t['dr'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section id="tab-calendario" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Calendario</h2>
    <div class="calendario-lista">
        <div class="match-row match-win">
            <span class="match-giornata">G12</span>
            <span class="match-team team-morra">Morra De Sanctis</span>
            <span class="match-score score-win">3 – 1</span>
            <span class="match-team team-right">Montella Academy</span>
            <span class="match-esito esito-v">V</span>
        </div>
        <div class="match-row match-future">
            <span class="match-giornata">G13</span>
            <span class="match-team">Teora</span>
            <span class="match-score score-future">07/03</span>
            <span class="match-team team-morra team-right">Morra De Sanctis</span>
            <span class="match-esito esito-future">–</span>
        </div>
    </div>
</section>

<section id="tab-rosa" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Rosa Ufficiale</h2>
    <div class="rosa-grid">
    <?php
    $res = pg_query($db, "SELECT * FROM giocatori ORDER BY ruolo DESC, nome ASC");
    $giocatori = pg_fetch_all($res);

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
    <?php 
        endforeach; 
    else:
        echo "<p style='color:white;'>Nessun giocatore trovato nel database.</p>";
    endif;
    ?>
    </div>
</section>

<script>
function apriTab(nome, btn) {
    document.querySelectorAll('.stab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.stab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + nome).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>