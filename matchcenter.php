<?php
session_start();
require_once 'includes/db_connect.php'; 
$id_partita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
include 'includes/header.php';

// 1. Recupero Dati della Partita
$resP = pg_query_params($db, "SELECT *, to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f FROM public.partite WHERE id = $1", [$id_partita]);
$match = pg_fetch_assoc($resP);

// 2. Recupero Eventi dalla tabella cronaca_live
$resEv = pg_query_params($db, "SELECT tipo_evento, testo FROM cronaca_live WHERE id_partita = $1", [$id_partita]);
$eventi_match = ['goal' => [], 'card' => [], 'red' => []];

if ($resEv) {
    while ($ev = pg_fetch_assoc($resEv)) {
        $eventi_match[$ev['tipo_evento']][] = strtolower($ev['testo']);
    }
}

/**
 * FUNZIONE PER ASSEGNARE GLI EVENTI AI GIOCATORI
 */
function ottieniEventiGiocatore($nome_completo, $eventi_match) {
    $nome_lower = strtolower($nome_completo);
    $parts = explode(' ', $nome_lower);
    $particelle = ['di', 'de', 'del', 'della', 'lo', 'la', 'da'];
    $is_composto = in_array($parts[0], $particelle);
    $cognome_principale = $is_composto ? $parts[0] . ' ' . $parts[1] : $parts[0];
    
    $omonimi = ['caputo', 'di pietro'];
    $serve_iniziale = false;
    foreach($omonimi as $o) { if(strpos($cognome_principale, $o) !== false) $serve_iniziale = true; }

    $output_html = '';
    $mappa_icone = ['goal' => '⚽', 'card' => '🟨', 'red' => '🟥'];

    foreach ($mappa_icone as $tipo => $icona) {
        foreach ($eventi_match[$tipo] as $testo_cronaca) {
            $assegna = false;
            if ($serve_iniziale) {
                $nome_battesimo = end($parts);
                $iniziale = substr($nome_battesimo, 0, 1);
                if (strpos($testo_cronaca, $nome_lower) !== false || 
                    strpos($testo_cronaca, $cognome_principale . ' ' . $iniziale) !== false) {
                    $assegna = true;
                }
            } else {
                if (strpos($testo_cronaca, $cognome_principale) !== false) {
                    $assegna = true;
                }
            }
            if ($assegna) $output_html .= '<span class="ev-icon">' . $icona . '</span>';
        }
    }
    return $output_html;
}

// Carichiamo i dati solo se l'utente è loggato per risparmiare risorse
if (isset($_SESSION['id_utente'])) {
    // 3. Titolari
    $resG = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo, fg.linea FROM formazione_giocatori fg JOIN giocatori g ON fg.id_giocatore = g.id WHERE fg.id_partita = $1 ORDER BY fg.linea ASC", [$id_partita]);
    $formazione = [];
    while ($row = pg_fetch_assoc($resG)) { $formazione[$row['linea']][] = $row; }

    // 4. Sostituzioni
    $resS = pg_query_params($db, "SELECT ge.nome as entra, gs.nome as esce FROM sostituzioni s JOIN giocatori ge ON s.id_entra = ge.id JOIN giocatori gs ON s.id_esce = gs.id WHERE s.id_partita = $1", [$id_partita]);
    $cambi = pg_fetch_all($resS) ?: [];

    // 5. Panchina
    $resB = pg_query_params($db, "SELECT g.nome FROM panchina p JOIN giocatori g ON p.id_giocatore = g.id WHERE p.id_partita = $1 AND p.id_giocatore NOT IN (SELECT id_entra FROM sostituzioni WHERE id_partita = $1)", [$id_partita]);
    $disposizione = pg_fetch_all($resB) ?: [];
}
?>

<link rel="stylesheet" href="css/style4.css">

<div class="match-center-wrapper">

    <div class="match-hero">
        <span class="label-lupi">Match Center 🐺</span>
        <div class="score-board">
            <div class="team-name"><?= htmlspecialchars($match['casa']) ?></div>
            <div class="score-box">
                <?= ($match['giocata'] == 't') ? $match['gol_casa'] . " - " . $match['gol_ospite'] : "VS" ?>
            </div>
            <div class="team-name"><?= htmlspecialchars($match['ospite']) ?></div>
        </div>
        <div class="match-meta">
            <span>📅 <?= $match['data_f'] ?></span> | <span>📍 Campo Comunale</span>
        </div>
    </div>

    <?php if (isset($_SESSION['id_utente'])): ?>
        
        <section class="pitch-container">
            <div class="pitch-grass">
                <div class="pitch-line-v"></div><div class="pitch-line-h"></div><div class="pitch-circle"></div>
                <?php foreach ([1,2,3,4,5] as $L): if (!empty($formazione[$L])): ?>
                    <div class="pitch-line level-<?= $L ?>">
                        <?php foreach ($formazione[$L] as $p): ?>
                            <div class="player-card">
                                <div class="player-events"><?= ottieniEventiGiocatore($p['nome'], $eventi_match) ?></div>
                                <div class="player-shirt"><?= substr($p['ruolo'], 0, 1) ?></div>
                                <?php 
                                    $n_parts = explode(' ', $p['nome']);
                                    $p_list = ['di', 'de', 'del', 'della', 'lo', 'la', 'da'];
                                    $label = (in_array(strtolower($n_parts[0]), $p_list) && isset($n_parts[1])) ? $n_parts[0].' '.$n_parts[1] : $n_parts[0];
                                ?>
                                <div class="player-name"><?= htmlspecialchars($label) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

        <div class="match-details-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px; max-width:800px; margin: 30px auto; padding:0 20px;">
            <div class="info-card" style="background:#161b22; padding:20px; border-radius:10px; border-top: 3px solid #4caf50;">
                <h3 style="color:#4caf50; margin-bottom:15px;">🔄 Sostituzioni</h3>
                <?php if (empty($cambi)): ?>
                    <p style="color:gray; font-size:0.9rem;">Nessun cambio effettuato.</p>
                <?php else: foreach ($cambi as $c): ?>
                    <div class="sub-row" style="margin-bottom:10px; border-bottom:1px solid #333; padding-bottom:5px;">
                        <span style="color:#4caf50; font-weight:bold;">▲ <?= htmlspecialchars($c['entra']) ?> <?= ottieniEventiGiocatore($c['entra'], $eventi_match) ?></span><br>
                        <span style="color:#f44336; font-size:0.8rem;">▼ <?= htmlspecialchars($c['esce']) ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="info-card" style="background:#161b22; padding:20px; border-radius:10px; border-top: 3px solid #8b949e;">
                <h3 style="color:#8b949e; margin-bottom:15px;">📋 A Disposizione</h3>
                <ul style="list-style:none; padding:0;">
                    <?php if (empty($disposizione)): ?>
                        <li style="color:gray; font-size:0.9rem;">Panchina vuota.</li>
                    <?php else: foreach ($disposizione as $d): ?>
                        <li style="margin-bottom:8px; font-size:0.9rem; color:white;">
                            <span style="opacity:0.5;">💺</span> <?= htmlspecialchars($d['nome']) ?> <?= ottieniEventiGiocatore($d['nome'], $eventi_match) ?>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>

    <?php else: ?>

        <div class="login-lock-message" style="text-align:center; margin: 60px auto; max-width: 500px; padding: 40px 20px; background: rgba(255,255,255,0.05); border-radius: 15px; border: 1px dashed #4caf50;">
            <div style="font-size: 3rem; margin-bottom: 20px;">🔒</div>
            <h2 style="color: #fff; margin-bottom: 10px;">Dettagli Riservati</h2>
            <p style="color: #ccc; line-height: 1.6; margin-bottom: 25px;">
                La formazione ufficiale, le sostituzioni e gli eventi live sono visibili solo ai membri del branco.
            </p>
            <a href="login.php" style="background: #4caf50; color: white; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; display: inline-block;">
                Accedi per vedere i dettagli
            </a>
        </div>

    <?php endif; ?>
    
    <div style="text-align:center; margin-top:30px; margin-bottom:50px;">
        <a href="stagione.php" style="color:#4caf50; text-decoration:none; font-weight:bold; border:1px solid #4caf50; padding:10px 25px; border-radius:5px; transition: 0.3s;" 
           onmouseover="this.style.background='#4caf50'; this.style.color='white';" 
           onmouseout="this.style.background='transparent'; this.style.color='#4caf50';">
            ← Torna al Calendario
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>