<?php
session_start();
require_once 'includes/db_connect.php'; 

$id_partita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
include 'includes/header.php';

// 1. RECUPERO DATI DELLA PARTITA
$resP = pg_query_params($db, "SELECT *, to_char(data_match, 'DD/MM/YYYY HH24:MI') as data_f FROM public.partite WHERE id = $1", [$id_partita]);
$match = pg_fetch_assoc($resP);

// 2. RECUPERO EVENTI DALLA CRONACA
$resEv = pg_query_params($db, "SELECT tipo_evento, testo FROM cronaca_live WHERE id_partita = $1", [$id_partita]);
$eventi_match = ['goal' => [], 'card' => [], 'red' => []];

if ($resEv) {
    while ($ev = pg_fetch_assoc($resEv)) {
        // Pulizia testo per il confronto
        $eventi_match[$ev['tipo_evento']][] = strtolower(trim($ev['testo']));
    }
}

/**
 * FUNZIONE OTTIMIZZATA PER ASSEGNARE GLI EVENTI
 * Gestisce omonimi richiedendo Nome + Cognome o Cognome + Iniziale
 */
function ottieniEventiGiocatore($nome_completo_db, $eventi_match, $nomi_totali_partita) {
    $nome_completo = strtolower(trim($nome_completo_db));
    $parts = explode(' ', $nome_completo);
    
    // Gestione particelle cognomi (es: "Di Pietro")
    $particelle = ['di', 'de', 'del', 'della', 'lo', 'la', 'da'];
    if (in_array($parts[0], $particelle) && isset($parts[1])) {
        $cognome_rif = $parts[0] . ' ' . $parts[1];
        $nome_rif = isset($parts[2]) ? $parts[2] : '';
    } else {
        $cognome_rif = $parts[0];
        $nome_rif = isset($parts[1]) ? $parts[1] : '';
    }

    // Rilevamento automatico omonimia sul cognome
    $count_omonimi = 0;
    foreach ($nomi_totali_partita as $n) {
        if (strpos(strtolower($n), $cognome_rif) === 0) $count_omonimi++;
    }

    $output_html = '';
    $mappa_icone = ['goal' => '⚽', 'card' => '🟨', 'red' => '🟥'];

    foreach ($mappa_icone as $tipo => $icona) {
        foreach ($eventi_match[$tipo] as $testo_cronaca) {
            $assegna = false;

            if ($count_omonimi > 1) {
                // --- CASO OMONIMIA: Serve precisione ---
                $iniziale = $nome_rif ? substr($nome_rif, 0, 1) : '';
                
                // Match "Cognome Nome", "Nome Cognome" o "Cognome I."
                $match_pieno = (strpos($testo_cronaca, $nome_completo) !== false);
                $match_inverso = ($nome_rif && strpos($testo_cronaca, $nome_rif . ' ' . $cognome_rif) !== false);
                $match_iniziale = ($iniziale && strpos($testo_cronaca, $cognome_rif . ' ' . $iniziale) !== false);

                if ($match_pieno || $match_inverso || $match_iniziale) {
                    $assegna = true;
                }
            } else {
                // --- CASO NORMALE: Basta il cognome ---
                if (strpos($testo_cronaca, $cognome_rif) !== false) {
                    $assegna = true;
                }
            }
            
            if ($assegna) {
                $output_html .= '<span class="ev-icon">' . $icona . '</span>';
            }
        }
    }
    return $output_html;
}

// 3. CARICAMENTO DATI (Solo se loggato) E PREPARAZIONE LISTA OMONIMI
$nomi_per_controllo = [];
$formazione = [];
$cambi = [];
$disposizione = [];

if (isset($_SESSION['id_utente'])) {
    // Titolari
    $resG = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo, fg.linea FROM formazione_giocatori fg JOIN giocatori g ON fg.id_giocatore = g.id WHERE fg.id_partita = $1 ORDER BY fg.linea ASC, fg.ordine_orizzontale ASC", [$id_partita]);
    while ($row = pg_fetch_assoc($resG)) { 
        $formazione[$row['linea']][] = $row; 
        $nomi_per_controllo[] = $row['nome'];
    }

    // Sostituzioni
    $resS = pg_query_params($db, "SELECT ge.nome as entra, gs.nome as esce FROM sostituzioni s JOIN giocatori ge ON s.id_entra = ge.id JOIN giocatori gs ON s.id_esce = gs.id WHERE s.id_partita = $1", [$id_partita]);
    $cambi = pg_fetch_all($resS) ?: [];
    foreach($cambi as $c) { $nomi_per_controllo[] = $c['entra']; }

    // Panchina
    $resB = pg_query_params($db, "SELECT g.nome FROM panchina p JOIN giocatori g ON p.id_giocatore = g.id WHERE p.id_partita = $1 AND p.id_giocatore NOT IN (SELECT id_entra FROM sostituzioni WHERE id_partita = $1)", [$id_partita]);
    $disposizione = pg_fetch_all($resB) ?: [];
    foreach($disposizione as $d) { $nomi_per_controllo[] = $d['nome']; }
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
                                <div class="player-events">
                                    <?= ottieniEventiGiocatore($p['nome'], $eventi_match, $nomi_per_controllo) ?>
                                </div>
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

        <div class="match-details-grid">
            <div class="info-card subs-card">
                <h3 class="subs-title">🔄 Sostituzioni</h3>
                <?php if (empty($cambi)): ?>
                    <p class="empty-msg">Nessun cambio effettuato.</p>
                <?php else: foreach ($cambi as $c): ?>
                    <div class="sub-row">
                        <span class="sub-in">▲ <?= htmlspecialchars($c['entra']) ?> <?= ottieniEventiGiocatore($c['entra'], $eventi_match, $nomi_per_controllo) ?></span><br>
                        <span class="sub-out">▼ <?= htmlspecialchars($c['esce']) ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="info-card bench-card">
                <h3 class="bench-title">📋 A Disposizione</h3>
                <ul class="bench-list">
                    <?php if (empty($disposizione)): ?>
                        <li class="empty-msg">Panchina vuota.</li>
                    <?php else: foreach ($disposizione as $d): ?>
                        <li class="bench-item">
                            <span class="bench-icon">💺</span> <?= htmlspecialchars($d['nome']) ?> 
                            <?= ottieniEventiGiocatore($d['nome'], $eventi_match, $nomi_per_controllo) ?>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>

    <?php else: ?>
        <div class="login-lock-message">
            <div class="lock-icon">🔒</div>
            <h2 class="lock-title">Dettagli Riservati</h2>
            <p class="lock-desc">La formazione ufficiale e gli eventi live sono visibili solo ai membri del branco.</p>
            <a href="login.php" class="lock-btn">Accedi per vedere i dettagli</a>
        </div>
    <?php endif; ?>
    
    <div class="back-link-container">
        <a href="stagione.php" class="btn-back">← Torna al Calendario</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>