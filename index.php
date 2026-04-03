<?php 
session_start(); 
require_once 'includes/db_connect.php'; 
include 'includes/header.php'; 

/**
 * Ottimizzazione query:
 * 1. Usiamo to_char per formattare la data direttamente dal database
 * 2. Selezioniamo solo i campi necessari
 */

// Query Prossima Partita
$sqlNext = "SELECT casa, ospite, giornata, 
            to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f 
            FROM public.partite 
            WHERE giocata = false 
            ORDER BY data_match ASC LIMIT 1";
$resNext = pg_query($db, $sqlNext);
$nextMatch = pg_fetch_assoc($resNext);

// Query Calendario (le 3 successive alla prima)
$sqlCal = "SELECT casa, ospite, 
           to_char(data_match, 'DD/MM') as data_f 
           FROM public.partite 
           WHERE giocata = false 
           ORDER BY data_match ASC 
           OFFSET 1 LIMIT 3";
$resCal = pg_query($db, $sqlCal);
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
        <h2 class="box-title">⚽ Prossima Gara (Giornata <?= $nextMatch['giornata'] ?? '' ?>)</h2>
        
        <?php if ($nextMatch): ?>
            <div class="match-vs-display">
                <div class="team-name <?= (strpos($nextMatch['casa'], 'Morra') !== false) ? 'text-highlight' : '' ?>">
                    <?= htmlspecialchars($nextMatch['casa']) ?>
                </div>
                <div class="vs-label">VS</div>
                <div class="team-name <?= (strpos($nextMatch['ospite'], 'Morra') !== false) ? 'text-highlight' : '' ?>">
                    <?= htmlspecialchars($nextMatch['ospite']) ?>
                </div>
            </div>
            
            <div class="match-info-footer">
                <span class="big-date">📅 <?= $nextMatch['data_f'] ?></span> 
                <span class="stadium-label">| 📍 Campo Comunale</span>
            </div>
        <?php else: ?>
            <p class="no-data">Nessun match in programma.</p>
        <?php endif; ?>
    </div>

    <div class="box-calendar">
        <h2 class="box-title">📅 Prossimi Impegni</h2>

        <?php 
        if (pg_num_rows($resCal) > 0):
            while ($c = pg_fetch_assoc($resCal)): 
        ?>
            <div class="cal-row">
                <div class="cal-date"><?= $c['data_f'] ?></div>
                <div class="cal-teams">
                    <span class="<?= (strpos($c['casa'], 'Morra') !== false) ? 'is-morra' : '' ?>">
                        <?= htmlspecialchars($c['casa']) ?>
                    </span>
                    <span class="vs-small">vs</span>
                    <span class="<?= (strpos($c['ospite'], 'Morra') !== false) ? 'is-morra' : '' ?>">
                        <?= htmlspecialchars($c['ospite']) ?>
                    </span>
                </div>
            </div>
        <?php 
            endwhile; 
        else:
            echo '<p class="no-data-small">Calendario in aggiornamento...</p>';
        endif; 
        ?>

        <div style="text-align: right; margin-top: 15px;">
            <a href="stagione.php" class="link-full">Vedi tutto il calendario →</a>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>