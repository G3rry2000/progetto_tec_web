<?php 
session_start(); 
require_once 'includes/db_connect.php'; 
include 'includes/header.php'; 

$isLogged = isset($_SESSION['id_utente']);
$nomeUtente = $isLogged ? ($_SESSION['nome'] ?? 'Lupo') : '';

// Query comuni per i box in basso (Partite)
$sqlNext = "SELECT casa, ospite, giornata, to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f FROM public.partite WHERE giocata = false ORDER BY data_match ASC LIMIT 1";
$resNext = pg_query($db, $sqlNext);
$nextMatch = pg_fetch_assoc($resNext);

$sqlCal = "SELECT casa, ospite, to_char(data_match, 'DD/MM') as data_f FROM public.partite WHERE giocata = false ORDER BY data_match ASC OFFSET 1 LIMIT 3";
$resCal = pg_query($db, $sqlCal);
?>

<div class="hero-home">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <?php if ($isLogged): ?>
            <h1 class="main-team-title">BENTORNATO, <?= strtoupper(htmlspecialchars($nomeUtente)) ?></h1>
            <p class="main-team-motto">Sosteniamo i lupi 🐺🟢⚪</p>
        <?php else: ?>
            <h1 class="main-team-title">ASD MORRA DE SANCTIS</h1>
            <p class="main-team-motto"> Il Branco è tornato 🐺🟢⚪</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($isLogged): ?>
    <div class="home-welcome" style="border-left: 5px solid var(--verde-squadra);">
        <h1>AREA SUPPORTER</h1>
        <p>In quanto membro del Branco, hai accesso alle statistiche avanzate dei giocatori e alla bacheca della Curva.</p>
        
        <div class="pulsanti-home">
            <a href="almanacco.php" class="btn-primary">📊 Analizza l'Almanacco</a>
            <a href="curva.php" class="btn-secondary">💬 Vai in Bacheca</a>
        </div>
    </div>
<?php else: ?>
    <div class="home-welcome">
        <h1>UNISCITI AL BRANCO!</h1>
        <p>Benvenuto sul sito ufficiale. Qui non troverai campioni milionari, ma la passione di un intero paese. 
           Registrati per vedere le medie voto dei giocatori e partecipare alle discussioni dei tifosi.</p>
        
        <div class="pulsanti-home">
            <a href="login.php" class="btn-primary">🔐 Accedi / Registrati</a>
            <a href="stagione.php" class="btn-secondary">🏆 Scopri la Stagione</a>
        </div>
    </div>
<?php endif; ?>

<div class="home-boxes-container">
    
    <div class="box-focus">
        <h2 class="box-title">⚽ PROSSIMA SFIDA</h2>
        <?php if ($nextMatch): ?>
            <div class="match-vs-display">
                <div class="team-name"><?= htmlspecialchars($nextMatch['casa']) ?></div>
                <div class="vs-label">VS</div>
                <div class="team-name"><?= htmlspecialchars($nextMatch['ospite']) ?></div>
            </div>
            <div class="match-info-footer" style="background: #f9f9f9; padding: 10px; border-radius: 8px;">
                <span class="big-date">📅 <?= $nextMatch['data_f'] ?></span> 
            </div>
        <?php else: ?>
            <p>Nessun match in programma.</p>
        <?php endif; ?>
    </div>

    <div class="box-calendar">
        <h2 class="box-title">📅 AGENDA LUPI</h2>
        <?php while ($c = pg_fetch_assoc($resCal)): ?>
            <div class="cal-row">
                <div class="cal-date"><?= $c['data_f'] ?></div>
                <div class="cal-teams">
                    <span class="is-morra"><?= htmlspecialchars($c['casa']) ?></span>
                    <span class="vs-small">vs</span>
                    <span><?= htmlspecialchars($c['ospite']) ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php if ($isLogged): ?>
    <div class="home-welcome" style="margin-top: 0; background: #222; color: white;">
        <h2 style="color: var(--verde-chiaro);">⚡ NEWS DALLA CURVA</h2>
        <p>Ci sono nuovi messaggi in bacheca. Non far mancare il tuo supporto!</p>
        <a href="curva.php" style="color: white; font-weight: bold;">Leggi i messaggi →</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>