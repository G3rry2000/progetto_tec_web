<?php
session_start();
require_once 'includes/db_connect.php'; 

// SICUREZZA: Solo chi è loggato può accedere
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$isAdmin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');
$msg_errore = "";

// 1. IDENTIFICA LA PARTITA LIVE (Default: la prossima non giocata)
$id_partita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$match_titolo = "Nessuna partita in corso";

if ($id_partita <= 0) {
    // Prende la prima partita non ancora giocata
    $resNext = pg_query($db, "SELECT id, casa, ospite FROM public.partite WHERE giocata = false ORDER BY data_match ASC LIMIT 1");
    if ($resNext && pg_num_rows($resNext) > 0) {
        $row = pg_fetch_assoc($resNext);
        $id_partita = $row['id'];
        $match_titolo = htmlspecialchars($row['casa'] . " - " . $row['ospite']);
    }
} else {
    // Prende la partita specifica dall'URL
    $resP = pg_query_params($db, "SELECT casa, ospite FROM public.partite WHERE id = $1", [$id_partita]);
    if ($resP && pg_num_rows($resP) > 0) {
        $row = pg_fetch_assoc($resP);
        $match_titolo = htmlspecialchars($row['casa'] . " - " . $row['ospite']);
    }
}

// 2. RECUPERA TUTTE LE PARTITE PER IL MENU A TENDINA
$tutte_le_partite = [];
$resTutte = pg_query($db, "SELECT id, giornata, casa, ospite FROM public.partite ORDER BY data_match ASC");
if ($resTutte) {
    $tutte_le_partite = pg_fetch_all($resTutte) ?: [];
}

// 3. LOGICA ADMIN: AGGIUNGI O ELIMINA EVENTI
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $minuto = trim($_POST['minuto']);
        $tipo_evento = $_POST['tipo_evento'];
        $testo = trim($_POST['testo']);

        if (!empty($minuto) && !empty($testo) && $id_partita > 0) {
            $q = "INSERT INTO public.cronaca_live (id_partita, minuto, tipo_evento, testo) VALUES ($1, $2, $3, $4)";
            pg_query_params($db, $q, [$id_partita, $minuto, $tipo_evento, $testo]);
            header("Location: live.php?id=" . $id_partita);
            exit;
        } else {
            $msg_errore = "Compila tutti i campi!";
        }
    } elseif (isset($_POST['delete_event'])) {
        $id_evento = (int)$_POST['id_evento'];
        pg_query_params($db, "DELETE FROM public.cronaca_live WHERE id = $1", [$id_evento]);
        header("Location: live.php?id=" . $id_partita);
        exit;
    }
}

// 4. RECUPERO EVENTI DAL DATABASE PER LA PARTITA SELEZIONATA
$eventi = [];
if ($id_partita > 0) {
    // Ordiniamo per ID decrescente in modo che l'evento più recente sia sempre in alto
    $resEv = pg_query_params($db, "SELECT * FROM public.cronaca_live WHERE id_partita = $1 ORDER BY id DESC", [$id_partita]);
    if ($resEv) {
        $eventi = pg_fetch_all($resEv) ?: [];
    }
}

include 'includes/header.php';
?>

<style>
    :root {
        --verde-lupi: #2e7d32;
        --verde-neon: #4caf50;
        --sfondo-dark: #0d1117;
        --panel-bg: #161b22;
        --rosso-live: #ff4444;
        --giallo-card: #ffeb3b;
        --blu-sub: #2196f3;
    }

    .live-hero {
        text-align: center;
        padding: 40px 20px;
        background: linear-gradient(180deg, #1a0505 0%, var(--sfondo-dark) 100%);
        border-bottom: 1px solid #30363d;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 68, 68, 0.1);
        color: var(--rosso-live);
        padding: 8px 16px;
        border-radius: 20px;
        border: 1px solid var(--rosso-live);
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .live-dot {
        width: 10px;
        height: 10px;
        background-color: var(--rosso-live);
        border-radius: 50%;
        animation: pulse-red 1.5s infinite;
    }

    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(255, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0); }
    }

    .live-hero h1 { font-size: 2.5rem; color: white; margin: 10px 0; font-weight: 900; }
    .live-hero p { color: #8b949e; font-size: 1.1rem; }

    .main-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }

    /* --- SELETTORE PARTITA --- */
    .match-selector {
        text-align: center;
        margin-bottom: 30px;
        background: var(--panel-bg);
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #30363d;
    }
    .match-selector select {
        padding: 10px;
        border-radius: 5px;
        background: #0d1117;
        color: white;
        border: 1px solid #444;
        font-size: 1rem;
        width: 100%;
        max-width: 400px;
        cursor: pointer;
    }

    /* --- PANNELLO ADMIN --- */
    .admin-live-panel {
        background: #0d1117;
        border: 1px solid #30363d;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 40px;
        box-shadow: 0 0 15px rgba(46, 125, 50, 0.2);
    }
    .admin-live-panel h3 { margin-top: 0; color: var(--verde-neon); display: flex; align-items: center; gap: 10px; }
    .form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .form-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 150px; }
    .form-group label { font-size: 0.8rem; color: #8b949e; }
    .form-group input, .form-group select, .form-group textarea {
        padding: 10px; background: var(--panel-bg); border: 1px solid #30363d; color: white; border-radius: 5px; width: 100%;
    }
    .btn-live { background: var(--verde-lupi); color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .btn-live:hover { background: var(--verde-neon); }
    .btn-delete { background: none; border: none; color: #f44336; cursor: pointer; font-size: 0.9rem; float: right; padding: 5px; }
    
    /* --- TIMELINE --- */
    .timeline {
        border-left: 2px solid #30363d;
        padding-left: 30px;
        margin-left: 20px;
    }
    .timeline-event {
        position: relative;
        margin-bottom: 30px;
        background: var(--panel-bg);
        padding: 15px 20px;
        border-radius: 10px;
        border: 1px solid #30363d;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .timeline-event::before {
        content: '';
        position: absolute; left: -37px; top: 20px;
        width: 12px; height: 12px; border-radius: 50%;
        border: 3px solid var(--sfondo-dark);
        background: #8b949e;
    }

    .tipo-goal::before { background: var(--verde-neon); box-shadow: 0 0 10px var(--verde-neon); }
    .tipo-goal .event-icon { color: var(--verde-neon); }
    .tipo-card::before { background: var(--giallo-card); }
    .tipo-card .event-icon { color: var(--giallo-card); }
    .tipo-sub::before { background: var(--blu-sub); }
    .tipo-sub .event-icon { color: var(--blu-sub); }
    .tipo-red::before { background: var(--rosso-live); box-shadow: 0 0 10px var(--rosso-live); }
    .tipo-red .event-icon { color: var(--rosso-live); }

    .event-time { font-weight: bold; font-size: 1.2rem; margin-bottom: 8px; color: white; }
    .event-text { color: #c9d1d9; font-size: 1.05rem; line-height: 1.5; white-space: pre-wrap; }
    .no-events { text-align: center; color: #8b949e; padding: 40px; font-style: italic; background: var(--panel-bg); border-radius: 10px; }
</style>

<div class="live-hero">
    <div class="live-badge">
        <div class="live-dot"></div>
        Radio Branco LIVE
    </div>
    <h1><?= $match_titolo ?></h1>
    <p>La radiocronaca testuale della partita, aggiornata in tempo reale.</p>
</div>

<div class="main-container">

    <div class="match-selector">
        <form method="GET" action="live.php">
            <label for="id_partita" style="color:#8b949e; display:block; margin-bottom:10px;">Seleziona una partita:</label>
            <select name="id" id="id_partita" onchange="this.form.submit()">
                <?php foreach ($tutte_le_partite as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $id_partita ? 'selected' : '' ?>>
                        Giornata <?= $p['giornata'] ?>: <?= htmlspecialchars($p['casa'] . " - " . $p['ospite']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($msg_errore): ?>
        <div style="background: #f44336; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align:center;">
            <?= $msg_errore ?>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
    <div class="admin-live-panel">
        <h3>🎙️ Cabina di Regia </h3>
        <form method="POST" action="live.php?id=<?= $id_partita ?>">
            <div class="form-row">
                <div class="form-group" style="flex: 0.5;">
                    <label>Minuto (es. 45', 90'+2)</label>
                    <input type="text" name="minuto" placeholder="Es. 12'" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Tipo Evento</label>
                    <select name="tipo_evento">
                        <option value="info">ℹ️ Info generica / Azione</option>
                        <option value="goal">⚽ GOAL!</option>
                        <option value="card">🟨 Ammonizione</option>
                        <option value="red">🟥 Espulsione</option>
                        <option value="sub">🔄 Sostituzione</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Racconto dell'azione</label>
                <textarea name="testo" rows="3" placeholder="Scrivi l'azione qui..." required></textarea>
            </div>
            <div style="text-align: right; margin-top: 15px;">
                <button type="submit" name="add_event" class="btn-live">Pubblica Aggiornamento 📢</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php if (empty($eventi)): ?>
        <div class="no-events">
            L'arbitro non ha ancora fischiato l'inizio, oppure nessun aggiornamento disponibile al momento. 🐺⚽
        </div>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($eventi as $ev): 
                $icona = "⏱️";
                if ($ev['tipo_evento'] === 'goal') $icona = "⚽";
                if ($ev['tipo_evento'] === 'card') $icona = "🟨";
                if ($ev['tipo_evento'] === 'red') $icona = "🟥";
                if ($ev['tipo_evento'] === 'sub') $icona = "🔄";
            ?>
                <div class="timeline-event tipo-<?= htmlspecialchars($ev['tipo_evento']) ?>">
                    
                    <?php if ($isAdmin): ?>
                        <form method="POST" action="live.php?id=<?= $id_partita ?>" onsubmit="return confirm('Vuoi davvero eliminare questo evento?');">
                            <input type="hidden" name="id_evento" value="<?= $ev['id'] ?>">
                            <button type="submit" name="delete_event" class="btn-delete" title="Elimina">🗑️</button>
                        </form>
                    <?php endif; ?>

                    <div class="event-time">
                        <span class="event-icon"><?= $icona ?></span> <?= htmlspecialchars($ev['minuto']) ?>
                    </div>
                    <div class="event-text"><?= nl2br(htmlspecialchars($ev['testo'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>