<?php
session_start();
require_once 'includes/db_connect.php';
include 'includes/header.php';
?>

<style>
/* ===== HERO ===== */
.stagione-hero {
    background: linear-gradient(135deg, #005c2b 0%, #003318 100%);
    text-align: center;
    padding: 40px 20px 30px;
    margin: -50px -50px 30px -50px; /* Sfonda fuori dal content-area */
    border-bottom: 3px solid var(--verde-squadra);
}
.stagione-label {
    font-size: 0.75rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #7ec896;
    margin-bottom: 6px;
}
.stagione-hero h1 {
    font-size: clamp(2rem, 5vw, 3.4rem);
    font-weight: 900;
    color: #fff;
    text-transform: uppercase;
    margin: 0;
}
.stagione-hero h1 span { color: var(--verde-squadra); }

/* ===== TAB NAV ===== */
.stagione-tabs {
    display: flex;
    border-bottom: 2px solid #ddd;
    margin-bottom: 28px;
    overflow-x: auto;
    scrollbar-width: none;
}
.stagione-tabs::-webkit-scrollbar { display: none; }

.stab-btn {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    color: #888;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 13px 24px;
    cursor: pointer;
    white-space: nowrap;
    transition: color 0.2s, border-color 0.2s;
}
.stab-btn:hover { color: #333; }
.stab-btn.active {
    color: var(--verde-squadra);
    border-bottom-color: var(--verde-squadra);
}

/* ===== PANNELLI ===== */
.stab-panel { display: none; animation: fadeIn 0.2s ease; }
.stab-panel.active { display: block; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to   { opacity: 1; transform: none; }
}

.stagione-section-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--verde-squadra);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 18px;
    padding-bottom: 6px;
    border-bottom: 2px solid var(--verde-squadra);
    display: inline-block;
}

/* ===== CLASSIFICA ===== */
.classifica-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.classifica-table thead th {
    background: #f0f0f0;
    color: #666;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 10px 8px;
    text-align: center;
    border-bottom: 2px solid #ddd;
}
.classifica-table thead th.col-nome { text-align: left; padding-left: 12px; }

.classifica-table tbody tr {
    border-bottom: 1px solid #eee;
    transition: background 0.15s;
}
.classifica-table tbody tr:hover { background: #f9f9f9; }

.classifica-table tbody td {
    padding: 11px 8px;
    text-align: center;
    color: #444;
}
.classifica-table tbody td.col-pos { color: #aaa; font-size: 0.78rem; width: 28px; }
.classifica-table tbody td.col-nome { text-align: left; padding-left: 12px; }
.classifica-table tbody td.col-pt { font-weight: 800; color: #111; font-size: 0.95rem; }

.riga-promo { border-left: 4px solid var(--verde-squadra); }
.riga-retro { border-left: 4px solid #c0392b; }
.riga-morra { background: rgba(0,148,68,0.07) !important; }

.nome-morra { color: var(--verde-squadra); font-weight: 700; }

.dr-pos { color: var(--verde-squadra); font-weight: 600; }
.dr-neg { color: #c0392b;              font-weight: 600; }

.classifica-legenda {
    margin-top: 10px;
    font-size: 0.72rem;
    color: #999;
}
.legenda-promo { color: var(--verde-squadra); font-weight: 600; }
.legenda-retro { color: #c0392b;              font-weight: 600; }

/* ===== CALENDARIO ===== */
.calendario-lista { display: flex; flex-direction: column; gap: 6px; }

.match-row {
    display: grid;
    grid-template-columns: 38px 1fr 68px 1fr 28px;
    align-items: center;
    gap: 8px;
    background: #f9f9f9;
    border-radius: 6px;
    padding: 12px 14px;
    border: 1px solid #eee;
    border-left: 4px solid #ddd;
    transition: border-color 0.15s, background 0.15s;
}
.match-row:hover { background: #f3f3f3; }

.match-win  { border-left-color: var(--verde-squadra); }
.match-lose { border-left-color: #c0392b; }
.match-draw { border-left-color: #e6a817; }
.match-future { opacity: 0.65; }

.match-giornata {
    font-size: 0.68rem;
    font-weight: 700;
    color: #aaa;
    text-align: center;
}
.match-team {
    font-size: 0.84rem;
    font-weight: 500;
    color: #444;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.team-morra { color: var(--verde-squadra); font-weight: 700; }
.team-right { text-align: right; }

.match-score {
    font-weight: 800;
    font-size: 1rem;
    text-align: center;
    color: #222;
}
.score-win  { color: var(--verde-squadra); }
.score-lose { color: #c0392b; }
.score-draw { color: #e6a817; }
.score-future { font-size: 0.72rem; font-weight: 600; color: #999; }

.match-esito {
    font-size: 0.68rem;
    font-weight: 700;
    text-align: center;
    padding: 3px 5px;
    border-radius: 3px;
}
.esito-v      { background: rgba(0,148,68,0.12); color: var(--verde-squadra); }
.esito-s      { background: rgba(192,57,43,0.1); color: #c0392b; }
.esito-p      { background: rgba(230,168,23,0.1);color: #b8860b; }
.esito-future { color: #ccc; }

.legenda-win  { color: var(--verde-squadra); font-weight: 600; }
.legenda-draw { color: #b8860b;              font-weight: 600; }
.legenda-lose { color: #c0392b;              font-weight: 600; }

/* ===== ROSA ===== */
.rosa-avviso {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-left: 4px solid #e6a817;
    border-radius: 6px;
    padding: 14px 18px;
    margin-bottom: 22px;
    font-size: 0.88rem;
    color: #7a5800;
    line-height: 1.6;
}
.btn-avviso {
    display: inline-block;
    margin-top: 8px;
    background: var(--verde-squadra);
    color: #fff !important;
    padding: 7px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.8rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    transition: background 0.2s;
}
.btn-avviso:hover { background: var(--verde-chiaro); }

.rosa-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}
.giocatore-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-top: 3px solid var(--verde-squadra);
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.giocatore-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,148,68,0.12);
}

.ruolo-badge {
    display: inline-block;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 3px;
    margin-bottom: 8px;
}
.badge-por { background: #fff3cd; color: #856404; }
.badge-dif { background: #d1f5e0; color: #155724; }
.badge-cen { background: #d0e8ff; color: #0a4a8a; }
.badge-att { background: #fde8e8; color: #921212; }

.card-nome {
    font-size: 0.96rem;
    font-weight: 700;
    color: #222;
    margin: 0 0 3px 0;
    line-height: 1.2;
}
.card-nascita {
    font-size: 0.73rem;
    color: #aaa;
    margin-bottom: 12px;
}

.card-stats {
    display: flex;
    gap: 6px;
    border-top: 1px solid #eee;
    padding-top: 11px;
}
.stat-box { flex: 1; text-align: center; }
.stat-num {
    display: block;
    font-weight: 800;
    font-size: 1.25rem;
    color: #333;
    line-height: 1;
}
.stat-gol .stat-num { color: var(--verde-squadra); }
.stat-lbl {
    display: block;
    font-size: 0.58rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #bbb;
    margin-top: 2px;
}

/* Anonimi: sfocatura */
.card-stats-blur .stat-num {
    color: #ddd;
    filter: blur(4px);
    user-select: none;
}
.card-login-msg {
    font-size: 0.73rem;
    color: #aaa;
    margin-top: 7px;
    font-style: italic;
}
.card-login-msg a { color: var(--verde-squadra); text-decoration: none; font-weight: 600; }
.card-login-msg a:hover { text-decoration: underline; }

/* ===== RESPONSIVE ===== */
@media (max-width: 650px) {
    .stagione-hero { margin: -50px -20px 24px -20px; padding: 30px 16px 22px; }
    .match-row { grid-template-columns: 28px 1fr 54px 1fr 22px; gap: 5px; padding: 10px 10px; }
    .match-team { font-size: 0.75rem; }
    .classifica-table { font-size: 0.75rem; }
    .classifica-table th:nth-child(n+8),
    .classifica-table td:nth-child(n+8) { display: none; }
}
</style>

<!-- HERO fuori dal flusso normale -->
<div class="stagione-hero">
    <p class="stagione-label">Terza Categoria · Avellino · Girone B</p>
    <h1>Stagione 2024<span>/2025</span></h1>
</div>

<!-- TAB NAV -->
<nav class="stagione-tabs" role="tablist">
    <button class="stab-btn active" onclick="apriTab('classifica',this)" role="tab">🏅 Classifica</button>
    <button class="stab-btn"        onclick="apriTab('calendario',this)"  role="tab">📅 Calendario</button>
    <button class="stab-btn"        onclick="apriTab('rosa',this)"        role="tab">👥 Rosa</button>
</nav>

<!-- ==================== CLASSIFICA ==================== -->
<section id="tab-classifica" class="stab-panel active" role="tabpanel">
    <h2 class="stagione-section-title">Classifica</h2>
    <table class="classifica-table">
        <thead>
            <tr>
                <th class="col-pos">#</th>
                <th class="col-nome">Squadra</th>
                <th>PT</th><th>G</th><th>V</th><th>N</th>
                <th>P</th><th>F</th><th>S</th><th>DR</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $classifica = [
            ['nome'=>'Castelfranci',             'pt'=>29,'g'=>11,'v'=>9,'n'=>2,'p'=>0, 'f'=>28,'s'=>2,  'dr'=>26],
            ['nome'=>'FC Montemarano',           'pt'=>26,'g'=>11,'v'=>8,'n'=>2,'p'=>1, 'f'=>35,'s'=>13, 'dr'=>22],
            ['nome'=>'Teora',                    'pt'=>24,'g'=>11,'v'=>7,'n'=>3,'p'=>1, 'f'=>33,'s'=>8,  'dr'=>25],
            ['nome'=>"Nusco '75",                'pt'=>22,'g'=>11,'v'=>7,'n'=>1,'p'=>3, 'f'=>29,'s'=>16, 'dr'=>13],
            ['nome'=>'Morra De Sanctis',         'pt'=>17,'g'=>11,'v'=>5,'n'=>2,'p'=>4, 'f'=>20,'s'=>19, 'dr'=>1,  'morra'=>true],
            ['nome'=>'Andretta',                 'pt'=>16,'g'=>11,'v'=>4,'n'=>4,'p'=>3, 'f'=>18,'s'=>16, 'dr'=>2],
            ['nome'=>'Montella Football Academy','pt'=>13,'g'=>11,'v'=>3,'n'=>4,'p'=>4, 'f'=>24,'s'=>19, 'dr'=>5],
            ['nome'=>'Sporting Paternopoli',     'pt'=>7, 'g'=>11,'v'=>2,'n'=>1,'p'=>8, 'f'=>12,'s'=>31, 'dr'=>-19],
            ['nome'=>'S.S. Giuseppe Siconolfi',  'pt'=>6, 'g'=>10,'v'=>1,'n'=>3,'p'=>6, 'f'=>13,'s'=>24, 'dr'=>-11],
            ['nome'=>'Villamaina',               'pt'=>3, 'g'=>11,'v'=>0,'n'=>3,'p'=>8, 'f'=>13,'s'=>39, 'dr'=>-26],
            ['nome'=>'Frigento Sturno Sq. B',    'pt'=>3, 'g'=>11,'v'=>0,'n'=>3,'p'=>8, 'f'=>12,'s'=>50, 'dr'=>-38],
        ];
       
        foreach ($classifica as $i => $t):
            $pos = $i + 1;
            $rc  = '';
            if ($pos <= 2)           $rc .= ' riga-promo';
            if ($pos >= 9)           $rc .= ' riga-retro';
            if (!empty($t['morra'])) $rc .= ' riga-morra';
            $nc  = !empty($t['morra']) ? 'nome-morra' : '';
            $drc = $t['dr'] > 0 ? 'dr-pos' : ($t['dr'] < 0 ? 'dr-neg' : '');
            $drl = $t['dr'] > 0 ? '+' . $t['dr'] : $t['dr'];
        ?>
        <tr class="<?= trim($rc) ?>">
            <td class="col-pos"><?= $pos ?></td>
            <td class="col-nome"><span class="<?= $nc ?>"><?= htmlspecialchars($t['nome']) ?></span></td>
            <td class="col-pt"><?= $t['pt'] ?></td>
            <td><?= $t['g'] ?></td><td><?= $t['v'] ?></td><td><?= $t['n'] ?></td>
            <td><?= $t['p'] ?></td><td><?= $t['f'] ?></td><td><?= $t['s'] ?></td>
            <td class="<?= $drc ?>"><?= $drl ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="classifica-legenda" style="margin-top:10px;">
        <span class="legenda-promo">▌ Promozione</span> &nbsp;&nbsp;
        <span class="legenda-retro">▌ Retrocessione / playout</span>
    </p>
</section>

<!-- ==================== CALENDARIO ==================== -->
<section id="tab-calendario" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Calendario Partite</h2>
    <div class="calendario-lista">
    <?php
    $partite = [
        ['g'=>1,  'casa'=>'Montella Football Academy','gc'=>2,   'gt'=>2,   'trf'=>'Morra De Sanctis',        'data'=>null,   'ok'=>true],
        ['g'=>2,  'casa'=>'Morra De Sanctis',        'gc'=>0,   'gt'=>1,   'trf'=>'Teora',                   'data'=>null,   'ok'=>true],
        ['g'=>3,  'casa'=>'Villamaina',               'gc'=>1,   'gt'=>3,   'trf'=>'Morra De Sanctis',        'data'=>null,   'ok'=>true],
        ['g'=>4,  'casa'=>'Morra De Sanctis',        'gc'=>1,   'gt'=>1,   'trf'=>'Sporting Paternopoli',     'data'=>null,   'ok'=>true],
        ['g'=>5,  'casa'=>'S.S. Giuseppe Siconolfi', 'gc'=>1,   'gt'=>3,   'trf'=>'Morra De Sanctis',        'data'=>null,   'ok'=>true],
        ['g'=>6,  'casa'=>'Morra De Sanctis',        'gc'=>0,   'gt'=>4,   'trf'=>'Castelfranci',             'data'=>null,   'ok'=>true],
        ['g'=>7,  'casa'=>'Frigento Sturno Sq. B',   'gc'=>1,   'gt'=>6,   'trf'=>'Morra De Sanctis',        'data'=>null,   'ok'=>true],
        ['g'=>8,  'casa'=>'Morra De Sanctis',        'gc'=>1,   'gt'=>0,   'trf'=>'Andretta',                 'data'=>null,   'ok'=>true],
        ['g'=>10, 'casa'=>'FC Montemarano',           'gc'=>3,   'gt'=>0,   'trf'=>'Morra De Sanctis',        'data'=>null,   'ok'=>true],
        ['g'=>11, 'casa'=>'Morra De Sanctis',        'gc'=>1,   'gt'=>4,   'trf'=>"Nusco '75",               'data'=>null,   'ok'=>true],
        ['g'=>12, 'casa'=>'Morra De Sanctis',        'gc'=>3,   'gt'=>1,   'trf'=>'Montella Football Academy','data'=>null,   'ok'=>true],
        ['g'=>13, 'casa'=>'Teora',                   'gc'=>null,'gt'=>null,'trf'=>'Morra De Sanctis',        'data'=>'07/03','ok'=>false],
        ['g'=>14, 'casa'=>'Morra De Sanctis',        'gc'=>null,'gt'=>null,'trf'=>'Villamaina',               'data'=>'15/03','ok'=>false],
    ];
    foreach ($partite as $p):
        $mc  = ($p['casa'] === 'Morra De Sanctis');
        $mg  = $mc ? $p['gc'] : $p['gt'];
        $ag  = $mc ? $p['gt'] : $p['gc'];

        $rc = 'match-row'; $sc = 'match-score'; $es = '';
        if ($p['ok']) {
            if ($mg > $ag)      { $rc .= ' match-win';  $sc .= ' score-win';  $es = 'v'; }
            elseif ($mg < $ag)  { $rc .= ' match-lose'; $sc .= ' score-lose'; $es = 's'; }
            else                { $rc .= ' match-draw'; $sc .= ' score-draw'; $es = 'p'; }
        } else {
            $rc .= ' match-future';
        }
        $cc = ($p['casa'] === 'Morra De Sanctis') ? 'match-team team-morra' : 'match-team';
        $tc = ($p['trf']  === 'Morra De Sanctis') ? 'match-team team-morra team-right' : 'match-team team-right';
    ?>
    <div class="<?= $rc ?>">
        <span class="match-giornata">G<?= $p['g'] ?></span>
        <span class="<?= $cc ?>"><?= htmlspecialchars($p['casa']) ?></span>
        <?php if ($p['ok']): ?>
            <span class="<?= $sc ?>"><?= $p['gc'] ?> – <?= $p['gt'] ?></span>
        <?php else: ?>
            <span class="match-score score-future"><?= $p['data'] ?></span>
        <?php endif; ?>
        <span class="<?= $tc ?>"><?= htmlspecialchars($p['trf']) ?></span>
        <span class="match-esito esito-<?= $es ?: 'future' ?>"><?= $es ? strtoupper($es) : '–' ?></span>
    </div>
    <?php endforeach; ?>
    </div>
    <p class="classifica-legenda" style="margin-top:10px;">
        <span class="legenda-win">V Vittoria</span> &nbsp;
        <span class="legenda-draw">P Pareggio</span> &nbsp;
        <span class="legenda-lose">S Sconfitta</span>
    </p>
</section>

<!-- ==================== ROSA ==================== -->
<section id="tab-rosa" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Rosa della Squadra</h2>

    <?php if (!isset($_SESSION['id_utente'])): ?>
    <div class="rosa-avviso">
        🔒 <strong>Sei un tifoso anonimo!</strong>
        Le statistiche complete sono riservate agli utenti registrati.<br>
        <a href="login.php" class="btn-avviso">Accedi o Registrati</a>
    </div>
    <?php endif; ?>

    <div class="rosa-grid">
    <?php
    $giocatori = [];
    try {
        $res = $pdo->query("SELECT * FROM giocatori ORDER BY ruolo, nome");
        $giocatori = $res->fetchAll();
    } catch (Exception $e) { $giocatori = []; }

    if (empty($giocatori)) {
        $giocatori = [
            ['nome'=>'Caputo Davide',      'nascita'=>'09-06-2006','ruolo'=>'CEN','gol'=>3,'presenze'=>1,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Caputo Giancarmine', 'nascita'=>'15-04-2000','ruolo'=>'CEN','gol'=>0,'presenze'=>0,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Carino Gerardo',     'nascita'=>'29-06-2000','ruolo'=>'CEN','gol'=>3,'presenze'=>1,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Chieffo Alessandro', 'nascita'=>'28-09-1995','ruolo'=>'CEN','gol'=>0,'presenze'=>1,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Ciccone Gabriele',   'nascita'=>'27-01-2005','ruolo'=>'DIF','gol'=>0,'presenze'=>0,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Covino Benedetto',   'nascita'=>'24-10-2006','ruolo'=>'POR','gol'=>0,'presenze'=>0,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'De Simone Bruno',    'nascita'=>'23-01-1998','ruolo'=>'ATT','gol'=>2,'presenze'=>0,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Di Leo Andrea',      'nascita'=>'14-05-2007','ruolo'=>'ATT','gol'=>0,'presenze'=>1,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Di Paola Rocco',     'nascita'=>'02-04-1985','ruolo'=>'ATT','gol'=>0,'presenze'=>0,'ammonizioni'=>0,'espulsioni'=>0],
            ['nome'=>'Di Paolo Pietro',    'nascita'=>'02-06-1998','ruolo'=>'CEN','gol'=>0,'presenze'=>1,'ammonizioni'=>0,'espulsioni'=>0],
        ];
    }

    $rnomi  = ['POR'=>'Portiere','DIF'=>'Difensore','CEN'=>'Centrocampista','ATT'=>'Attaccante'];
    $rclass = ['POR'=>'badge-por','DIF'=>'badge-dif','CEN'=>'badge-cen','ATT'=>'badge-att'];

    foreach ($giocatori as $g):
        $rk = strtoupper($g['ruolo'] ?? 'CEN');
        $hg = ($g['gol'] ?? 0) > 0;
    ?>
    <article class="giocatore-card">
        <span class="ruolo-badge <?= $rclass[$rk] ?? 'badge-cen' ?>"><?= $rnomi[$rk] ?? $rk ?></span>
        <h3 class="card-nome"><?= htmlspecialchars($g['nome']) ?></h3>
        <p class="card-nascita">🎂 <?= htmlspecialchars($g['nascita'] ?? '') ?></p>

        <?php if (isset($_SESSION['id_utente'])): ?>
        <div class="card-stats">
            <div class="stat-box <?= $hg ? 'stat-gol' : '' ?>">
                <span class="stat-num"><?= $g['gol'] ?? 0 ?></span>
                <span class="stat-lbl">Gol</span>
            </div>
            <div class="stat-box">
                <span class="stat-num"><?= $g['presenze'] ?? 0 ?></span>
                <span class="stat-lbl">Pres.</span>
            </div>
            <div class="stat-box">
                <span class="stat-num"><?= $g['ammonizioni'] ?? 0 ?></span>
                <span class="stat-lbl">🟨</span>
            </div>
            <div class="stat-box">
                <span class="stat-num"><?= $g['espulsioni'] ?? 0 ?></span>
                <span class="stat-lbl">🟥</span>
            </div>
        </div>
        <?php else: ?>
        <div class="card-stats card-stats-blur">
            <div class="stat-box"><span class="stat-num">0</span><span class="stat-lbl">Gol</span></div>
            <div class="stat-box"><span class="stat-num">0</span><span class="stat-lbl">Pres.</span></div>
            <div class="stat-box"><span class="stat-num">0</span><span class="stat-lbl">🟨</span></div>
            <div class="stat-box"><span class="stat-num">0</span><span class="stat-lbl">🟥</span></div>
        </div>
        <p class="card-login-msg"><a href="login.php">Accedi</a> per vedere le statistiche</p>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
    </div>
</section>

<script>
function apriTab(nome, btn) {
    document.querySelectorAll('.stab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.stab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('tab-' + nome).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>