<?php 
session_start(); 
require_once 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Query per i box in basso (Prossima partita e Calendario)
$resNext = pg_query($db, "SELECT * FROM public.partite WHERE giocata = 'f' ORDER BY giornata ASC LIMIT 1");
$nextMatch = pg_fetch_assoc($resNext);

$resCal = pg_query($db, "SELECT * FROM public.partite WHERE giocata = 'f' ORDER BY giornata ASC OFFSET 1 LIMIT 3");
?>

<div class="hero-home">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="main-team-title">ASD MORRA DE SANCTIS</h1>
        <p class="main-team-motto">Il Branco è tornato. 🐺🟢⚪</p>
    </div>
</div>

<div class="home-welcome">
    <h1>BENVENUTI NELLA TANA DEL LUPO!</h1>
    <p>
        Dimenticate i campi in erba perfetta e i riflettori da stadio. Qui si respira l'essenza vera del calcio: 
        <strong>sudore, terra battuta e orgoglio biancoverde.</strong>
    </p>

    <div class="pulsanti-home">
        <a href="stagione.php" class="btn-primary">🏆 La Stagione</a>
        <a href="curva.php" class="btn-secondary">📣 La Curva</a>
    </div>
</div>

<div class="home-boxes-container">

    <div class="box-focus">
        <h2 class="box-title">⚽ Prossima Gara</h2>
        
        <?php if ($nextMatch): ?>
        <div class="match-vs-display">
            <div class="team-name" style="color: <?= (strpos($nextMatch['casa'], 'Morra') !== false) ? 'var(--verde-squadra)' : '#333' ?>;">
                <?= htmlspecialchars($nextMatch['casa']) ?>
            </div>
            <div class="vs-label">VS</div>
            <div class="team-name" style="color: <?= (strpos($nextMatch['ospite'], 'Morra') !== false) ? 'var(--verde-squadra)' : '#333' ?>;">
                <?= htmlspecialchars($nextMatch['ospite']) ?>
            </div>
        </div>
        
        <div class="match-info-footer">
            <span class="big-date">📅 <?= $nextMatch['data_match'] ?></span> 
            <span class="stadium-label">| 📍 Morra De Sanctis</span>
        </div>
        <?php else: ?>
            <p style="text-align:center; padding:20px; color:#888;">Nessun match in programma.</p>
        <?php endif; ?>
    </div>

    <div class="box-calendar">
        <h2 class="box-title">📅 Prossimi Impegni</h2>

        <?php while ($c = pg_fetch_assoc($resCal)): ?>
        <div class="cal-row">
            <div class="cal-date"><?= $c['data_match'] ?></div>
            <div class="cal-teams">
                <span class="<?= (strpos($c['casa'], 'Morra') !== false) ? 'is-morra' : '' ?>"><?= htmlspecialchars($c['casa']) ?></span>
                <span class="vs-small">vs</span>
                <span class="<?= (strpos($c['ospite'], 'Morra') !== false) ? 'is-morra' : '' ?>"><?= htmlspecialchars($c['ospite']) ?></span>
            </div>
        </div>
        <?php endwhile; ?>

        <div style="text-align: right; margin-top: 15px;">
            <a href="stagione.php" class="link-full">Vedi tutto →</a>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>