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

//IDENTIFICA LA PARTITA LIVE
$id_partita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$match_titolo = "Nessuna partita in corso";

if ($id_partita <= 0) {
    $resNext = pg_query($db, "SELECT id, casa, ospite FROM public.partite WHERE giocata = false ORDER BY data_match ASC LIMIT 1");
    if ($resNext && pg_num_rows($resNext) > 0) {
        $row = pg_fetch_assoc($resNext);
        $id_partita = $row['id'];
        $match_titolo = htmlspecialchars($row['casa'] . " - " . $row['ospite']);
    }
} else {
    $resP = pg_query_params($db, "SELECT casa, ospite FROM public.partite WHERE id = $1", [$id_partita]);
    if ($resP && pg_num_rows($resP) > 0) {
        $row = pg_fetch_assoc($resP);
        $match_titolo = htmlspecialchars($row['casa'] . " - " . $row['ospite']);
    }
}

// RECUPERA TUTTE LE PARTITE
$tutte_le_partite = [];
$resTutte = pg_query($db, "SELECT id, giornata, casa, ospite FROM public.partite ORDER BY data_match ASC");
if ($resTutte) {
    $tutte_le_partite = pg_fetch_all($resTutte) ?: [];
}

//LOGICA ADMIN: AGGIUNGI O ELIMINA EVENTI
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

// 4. RECUPERO EVENTI
$eventi = [];
if ($id_partita > 0) {
    $resEv = pg_query_params($db, "SELECT * FROM public.cronaca_live WHERE id_partita = $1 ORDER BY id DESC", [$id_partita]);
    if ($resEv) {
        $eventi = pg_fetch_all($resEv) ?: [];
    }
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="css/styleLive.css">

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