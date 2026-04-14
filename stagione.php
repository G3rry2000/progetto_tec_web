<?php
session_start();
require_once 'includes/db_connect.php'; 
include 'includes/header.php';

// Gestione pulita dei booleani di Postgres
function is_pg_true($val) {
    return ($val === 't' || $val === true || $val === '1' || $val === 1);
}
?>

<link rel="stylesheet" href="css/style3.css?v=<?php echo time(); ?>">



<div class="stagione-hero">
    <p class="stagione-label">Terza Categoria · Avellino · Girone B</p>
    <h1>Stagione 2025<span>/2026</span></h1>
</div>

<nav class="stagione-tabs" role="tablist">
    <button class="stab-btn active" onclick="apriTab('classifica',this)" role="tab">🏅 Classifica</button>
    <button class="stab-btn" onclick="apriTab('calendario',this)" role="tab">📅 Calendario</button>
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
        $res = pg_query($db, "SELECT * FROM public.classifica ORDER BY pt DESC, dr DESC");
        $pos = 1;
        while ($t = pg_fetch_assoc($res)):
            $isMorra = is_pg_true($t['is_morra']);
            $rowClass = ($isMorra) ? 'riga-morra' : ($pos <= 2 ? 'riga-promo' : '');
        ?>
            <tr class="<?= $rowClass ?>">
                <td class="col-pos"><?= $pos++ ?></td>
                <td class="col-nome <?= ($isMorra) ? 'nome-morra' : '' ?>">
                    <?= htmlspecialchars($t['squadra']) ?>
                </td>
                <td class="col-pt"><strong><?= $t['pt'] ?></strong></td>
                <td><?= $t['g'] ?></td><td><?= $t['v'] ?></td><td><?= $t['n'] ?></td><td><?= $t['p'] ?></td>
                <td><?= $t['dr'] > 0 ? '+' . $t['dr'] : $t['dr'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</section>

<section id="tab-calendario" class="stab-panel" role="tabpanel">
    <h2 class="stagione-section-title">Calendario & Risultati</h2>
    <div class="calendario-lista">
        <?php
        // Recuperiamo le date già formattate da Postgres
        $resPartite = pg_query($db, "SELECT *, to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f FROM public.partite ORDER BY giornata ASC");
        while ($p = pg_fetch_assoc($resPartite)):
            $isMorraCasa = (stripos($p['casa'], 'Morra') !== false);
            $giocata = is_pg_true($p['giocata']);
            
            $mClass = 'match-future'; $esito = '–'; $eClass = 'esito-future';

            if ($giocata) {
                $golMorra = $isMorraCasa ? $p['gol_casa'] : $p['gol_ospite'];
                $golAvv = $isMorraCasa ? $p['gol_ospite'] : $p['gol_casa'];
                
                if ($golMorra > $golAvv) { $mClass = 'match-win'; $esito = 'V'; $eClass = 'esito-v'; }
                elseif ($golMorra < $golAvv) { $mClass = 'match-lose'; $esito = 'S'; $eClass = 'esito-s'; }
                else { $mClass = 'match-draw'; $esito = 'P'; $eClass = 'esito-p'; }
            }
        ?>
        <a href="matchcenter.php?id=<?= $p['id'] ?>" class="match-link">
            <div class="match-row <?= $mClass ?>">
                <span class="match-giornata">G<?= $p['giornata'] ?></span>
                <span class="match-team <?= $isMorraCasa ? 'team-morra' : '' ?>"><?= htmlspecialchars($p['casa']) ?></span>
                
                <?php if ($giocata): ?>
                    <span class="match-score">
                        <?= $p['gol_casa'] ?> – <?= $p['gol_ospite'] ?>
                    </span>
                <?php else: ?>
                    <span class="match-score score-future"><?= $p['data_f'] ?></span>
                <?php endif; ?>
                
                <span class="match-team team-right <?= !$isMorraCasa ? 'team-morra' : '' ?>"><?= htmlspecialchars($p['ospite']) ?></span>
                <span class="match-esito <?= $eClass ?>"><?= $esito ?></span>
            </div>
        </a>
        <?php endwhile; ?>
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