<?php 
session_start(); 
require_once 'includes/db_connect.php'; 

$css_extra = "styleHomepage.css"; 

include 'includes/header.php'; 

// Controllo sessione utente
$isLogged = isset($_SESSION['id_utente']);
$nomeUtente = $isLogged ? ($_SESSION['nome'] ?? 'Lupo') : '';

//  QUERY DATABASE
// Prossima partita (la più vicina nel tempo non ancora giocata)
$sqlNext = "SELECT casa, ospite, giornata, to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f FROM public.partite WHERE giocata = false ORDER BY data_match ASC LIMIT 1";
$resNext = pg_query($db, $sqlNext);
$nextMatch = pg_fetch_assoc($resNext);

// Agenda (le 3 partite successive alla prossima)
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
            <p class="main-team-motto">Il Branco è tornato 1982 🐺🟢⚪</p>
        <?php endif; ?>
    </div>
</div>

<section class="home-welcome-section">
    <?php if ($isLogged): ?>
        <div class="home-welcome supporter-border">
            <h1>AREA SUPPORTER</h1>
            <p>In quanto membro del Branco, hai accesso alle statistiche avanzate dei giocatori e alla bacheca della Curva.</p>
            <div class="pulsanti-home">
                <a href="card.php" class="btn-primary">📊 Analizza l'Almanacco</a>
                <a href="stagione.php" class="btn-secondary">🏆 Vai in Stagione</a>
            </div>
        </div>
    <?php else: ?>
        <div class="home-welcome">
            <h1>UNISCITI AL BRANCO!</h1>
            <p>Benvenuto sul sito ufficiale. Qui non troverai campioni milionari, ma la passione di un intero paese. 
               Registrati per vedere le medie voto dei giocatori e partecipare alle discussioni.</p>
            <div class="pulsanti-home">
                <a href="login.php" class="btn-primary">🔐 Accedi / Registrati</a>
                <a href="stagione.php" class="btn-secondary">🏆 Scopri la Stagione</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="home-boxes-container">
    
    <div class="box-focus">
        <h2 class="box-title">⚽ PROSSIMA SFIDA</h2>
        <?php if ($nextMatch): ?>
            <div class="match-vs-display">
                <div class="team-name"><?= htmlspecialchars($nextMatch['casa']) ?></div>
                <div class="vs-label">VS</div>
                <div class="team-name"><?= htmlspecialchars($nextMatch['ospite']) ?></div>
            </div>
            <div class="match-info-footer">
                <span class="big-date">📅 <?= $nextMatch['data_f'] ?></span> 
            </div>
        <?php else: ?>
            <div class="no-match">
                <p>Nessun match in programma al momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="box-calendar">
        <h2 class="box-title">📅 AGENDA LUPI</h2>
        <div class="cal-rows-wrapper">
            <?php if (pg_num_rows($resCal) > 0): ?>
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
            <?php else: ?>
                <p class="empty-cal">Calendario in aggiornamento...</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php if ($isLogged): ?>
    <div class="home-welcome dark-welcome">
        <h2 class="news-title">⚡ NEWS DALLA CURVA</h2>
        <p>Ci sono nuovi messaggi in bacheca. Non far mancare il tuo supporto!</p>
        <a href="curva.php" class="news-link">Leggi i messaggi →</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>