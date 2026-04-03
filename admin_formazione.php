<?php
session_start();
require_once 'includes/db_connect.php';

// Controllo Accesso
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') die("Accesso Negato");

$id_partita = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($id_partita <= 0) {
    $resNext = pg_query($db, "SELECT id FROM partite WHERE giocata = false ORDER BY giornata ASC LIMIT 1");
    $id_partita = ($resNext && pg_num_rows($resNext) > 0) ? pg_fetch_result($resNext, 0, 'id') : 1;
}

// RECUPERO MODULO SALVATO
$resM = pg_query_params($db, "SELECT modulo FROM formazioni_setup WHERE id_partita = $1", [$id_partita]);
$modulo_attuale = (pg_num_rows($resM) > 0) ? trim(pg_fetch_result($resM, 0, 'modulo')) : "4-4-2";

// --- LOGICA AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $id_g = (int)($_POST['id_g'] ?? 0);

    switch($_POST['ajax_action']) {
        case 'save_pos': 
            pg_query_params($db, "DELETE FROM panchina WHERE id_partita=$1 AND id_giocatore=$2", [$id_partita, $id_g]);
            pg_query_params($db, "INSERT INTO formazione_giocatori (id_partita, id_giocatore, linea, ordine_orizzontale) 
                                  VALUES ($1, $2, $3, $4) ON CONFLICT (id_partita, id_giocatore) 
                                  DO UPDATE SET linea=$3, ordine_orizzontale=$4", [$id_partita, $id_g, $_POST['linea'], $_POST['ordine']]);
            break;

        case 'swap_players':
            $id_1 = (int)$_POST['id_1'];
            $id_2 = (int)$_POST['id_2'];
            $resP1 = pg_query_params($db, "SELECT linea, ordine_orizzontale FROM formazione_giocatori WHERE id_partita=$1 AND id_giocatore=$2", [$id_partita, $id_1]);
            $pos1 = pg_fetch_assoc($resP1);
            if ($pos1) {
                pg_query_params($db, "UPDATE formazione_giocatori SET linea=$3, ordine_orizzontale=$4 WHERE id_partita=$1 AND id_giocatore=$2", 
                                [$id_partita, $id_1, $_POST['linea_target'], $_POST['ordine_target']]);
                pg_query_params($db, "UPDATE formazione_giocatori SET linea=$3, ordine_orizzontale=$4 WHERE id_partita=$1 AND id_giocatore=$2", 
                                [$id_partita, $id_2, $pos1['linea'], $pos1['ordine_orizzontale']]);
            }
            break;
        
        case 'add_bench': 
            pg_query_params($db, "DELETE FROM formazione_giocatori WHERE id_partita=$1 AND id_giocatore=$2", [$id_partita, $id_g]);
            pg_query_params($db, "INSERT INTO panchina (id_partita, id_giocatore) VALUES ($1, $2) ON CONFLICT DO NOTHING", [$id_partita, $id_g]);
            break;

        case 'make_sub':
            $id_esce = (int)$_POST['id_esce'];
            $id_entra = (int)$_POST['id_entra'];
            pg_query_params($db, "INSERT INTO sostituzioni (id_partita, id_esce, id_entra) VALUES ($1, $2, $3)", [$id_partita, $id_esce, $id_entra]);
            break;

        case 'delete_sub':
            pg_query_params($db, "DELETE FROM sostituzioni WHERE id=$1", [(int)$_POST['id_sub']]);
            break;

        case 'change_modulo':
            pg_query_params($db, "DELETE FROM formazione_giocatori WHERE id_partita = $1", [$id_partita]);
            pg_query_params($db, "INSERT INTO formazioni_setup (id_partita, modulo) VALUES ($1, $2) ON CONFLICT (id_partita) DO UPDATE SET modulo=$2", [$id_partita, $_POST['modulo']]);
            break;

        case 'remove': 
            pg_query_params($db, "DELETE FROM formazione_giocatori WHERE id_partita=$1 AND id_giocatore=$2", [$id_partita, $id_g]);
            pg_query_params($db, "DELETE FROM panchina WHERE id_partita=$1 AND id_giocatore=$2", [$id_partita, $id_g]);
            break;
    }
    echo json_encode(['status' => 'ok']); exit;
}

include 'includes/header.php';
?>

<style>
    :root { --v-morra: #2e7d32; --dark: #0d1117; --panel: #161b22; }
    .admin-layout { display: grid; grid-template-columns: 260px 1fr 280px; gap: 15px; min-height: calc(100vh - 80px); padding: 15px; background: var(--dark); color: white; }
    .sidebar { background: var(--panel); padding: 15px; border-radius: 8px; border: 1px solid #30363d; overflow-y: auto; }
    .field-area { display: flex; flex-direction: column; align-items: center; }
    .soccer-pitch { width: 450px; height: 620px; background: #2d5a27; border: 3px solid rgba(255,255,255,0.5); position: relative; border-radius: 8px; margin-top: 10px; }
    .drop-spot { position: absolute; width: 60px; height: 60px; border: 2px dashed rgba(255,255,255,0.2); border-radius: 50%; transform: translate(-50%, -50%); display: flex; align-items: center; justify-content: center; }
    
    /* Token Giocatore in campo */
    .player-token { width: 52px; height: 52px; background: white; color: black; border-radius: 50%; font-weight: bold; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; cursor: grab; box-shadow: 0 4px 10px rgba(0,0,0,0.5); z-index: 10; }
    .player-token .name { font-size: 9px; line-height: 1; }
    .player-token .role { font-size: 7px; color: #666; font-weight: normal; margin-top: 1px; }
    
    .player-item { background: #21262d; padding: 10px; margin-bottom: 8px; border-radius: 5px; cursor: grab; font-size: 13px; border: 1px solid #30363d; position: relative; }
    .player-item.schierato { opacity: 0.4; border-left: 4px solid #4caf50; pointer-events: none; }
    .bench-area { min-height: 120px; border: 2px dashed #30363d; border-radius: 8px; padding: 10px; margin-bottom: 20px; }
    .remove-btn { position: absolute; top: -5px; right: -5px; background: #f44336; color: white; border: none; border-radius: 50%; width: 18px; height: 18px; cursor: pointer; font-size: 10px; z-index: 15; }
    
    .sub-form { background: #0d1117; padding: 12px; border-radius: 6px; border: 1px solid #333; margin-bottom: 20px; }
    .sub-form select { width: 100%; margin: 5px 0 12px; background: #161b22; color: white; border: 1px solid #444; padding: 5px; font-size: 12px; }
    .sub-form button { width: 100%; background: #2e7d32; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    
    .sub-item { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #333; position: relative; }
    .del-sub-btn { float: right; color: #f44336; cursor: pointer; font-size: 14px; background: none; border: none; }
</style>

<div class="admin-layout">
    <aside class="sidebar">
        <h3>Giornata</h3>
        <select onchange="location.href='admin_formazione.php?id='+this.value" style="width:100%; padding:8px; margin-bottom:20px; background:#0d1117; color:white;">
            <?php
            $resP = pg_query($db, "SELECT * FROM partite ORDER BY giornata ASC");
            while($p = pg_fetch_assoc($resP)) {
                $sel = ($p['id'] == $id_partita) ? 'selected' : '';
                echo "<option value='{$p['id']}' $sel>G{$p['giornata']} vs {$p['ospite']}</option>";
            }
            ?>
        </select>
        <h4>Rosa Disponibile</h4>
        <div id="pool">
            <?php
            $resOccupati = pg_query_params($db, "SELECT id_giocatore FROM formazione_giocatori WHERE id_partita=$1 UNION SELECT id_giocatore FROM panchina WHERE id_partita=$1", [$id_partita]);
            $occupati = pg_fetch_all_columns($resOccupati, 0) ?: [];
            $resG = pg_query($db, "SELECT * FROM giocatori ORDER BY ruolo, nome ASC");
            while($g = pg_fetch_assoc($resG)): 
                $isOccupato = in_array($g['id'], $occupati);
            ?>
                <div class="player-item <?= $isOccupato ? 'schierato' : '' ?>" draggable="<?= $isOccupato ? 'false' : 'true' ?>" ondragstart="onDragStart(event, 'pool')" data-id="<?= $g['id'] ?>">
                    <small style="color:#8b949e;"><?= $g['ruolo'] ?></small> - <?= htmlspecialchars($g['nome']) ?>
                </div>
            <?php endwhile; ?>
        </div>
    </aside>

    <main class="field-area">
        <div style="background:var(--panel); padding:10px; border-radius:8px;">
            Modulo: 
            <select id="modulo-select" onchange="cambiaModulo(this.value)">
                <option value="4-4-2" <?= $modulo_attuale=='4-4-2'?'selected':'' ?>>4-4-2</option>
                <option value="4-3-3" <?= $modulo_attuale=='4-3-3'?'selected':'' ?>>4-3-3</option>
                <option value="3-5-2" <?= $modulo_attuale=='3-5-2'?'selected':'' ?>>3-5-2</option>
            </select>
        </div>
        <div class="soccer-pitch" id="pitch"></div>
    </main>

    <aside class="sidebar">
        <h4>Panchina Corrente</h4>
        <div class="bench-area" id="bench-zone" ondragover="event.preventDefault()" ondrop="onDropBench(event)">
            <?php
            $resB = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo FROM panchina p JOIN giocatori g ON p.id_giocatore = g.id WHERE p.id_partita = $1", [$id_partita]);
            $giocatori_panchina = [];
            while($b = pg_fetch_assoc($resB)): 
                $giocatori_panchina[] = $b;
            ?>
                <div class="player-item" draggable="true" ondragstart="onDragStart(event, 'bench')" data-id="<?= $b['id'] ?>">
                    <small style="color:#8b949e;"><?= $b['ruolo'] ?></small> - <?= htmlspecialchars($b['nome']) ?>
                    <button class="remove-btn" onclick="removePlayer(<?= $b['id'] ?>)">×</button>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="sub-form">
            <h4 style="margin-top:0;">Nuova Sostituzione</h4>
            <label>Esce (Campo):</label>
            <select id="sub-esce">
                <option value="">-- Seleziona --</option>
                <?php
                $resC = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo FROM formazione_giocatori fg JOIN giocatori g ON fg.id_giocatore = g.id WHERE fg.id_partita = $1 ORDER BY g.ruolo", [$id_partita]);
                while($c = pg_fetch_assoc($resC)) echo "<option value='{$c['id']}'>[{$c['ruolo']}] {$c['nome']}</option>";
                ?>
            </select>
            
            <label>Entra (Panchina):</label>
            <select id="sub-entra">
                <option value="">-- Seleziona --</option>
                <?php foreach($giocatori_panchina as $gp) echo "<option value='{$gp['id']}'>[{$gp['ruolo']}] {$gp['nome']}</option>"; ?>
            </select>
            
            <button onclick="eseguiSostituzione()">Registra Cambio</button>
        </div>

        <h4>Log Sostituzioni</h4>
        <div id="sub-log" style="font-size: 0.8em; color: #aaa;">
            <?php
            $resS = pg_query_params($db, "SELECT s.id, g1.nome as entra, g2.nome as esce FROM sostituzioni s JOIN giocatori g1 ON s.id_entra = g1.id JOIN giocatori g2 ON s.id_esce = g2.id WHERE s.id_partita = $1 ORDER BY s.id DESC", [$id_partita]);
            while($s = pg_fetch_assoc($resS)): ?>
                <div class="sub-item">
                    <button class="del-sub-btn" onclick="deleteSub(<?= $s['id'] ?>)">🗑</button>
                    <b style='color:#4caf50'>IN:</b> <?= $s['entra'] ?> <br> 
                    <b style='color:#f44336'>OUT:</b> <?= $s['esce'] ?>
                </div>
            <?php endwhile; ?>
        </div>
    </aside>
</div>

<script>
const idPartita = <?= $id_partita ?>;
let moduloAttuale = "<?= $modulo_attuale ?>";

const schemi = {
    "4-4-2": [
        {y:92, x:50, l:1, o:1}, {y:75, x:15, l:2, o:1}, {y:75, x:38, l:2, o:2}, {y:75, x:62, l:2, o:3}, {y:75, x:85, l:2, o:4},
        {y:45, x:15, l:3, o:1}, {y:45, x:38, l:3, o:2}, {y:45, x:62, l:3, o:3}, {y:45, x:85, l:3, o:4}, {y:15, x:35, l:5, o:1}, {y:15, x:65, l:5, o:2}
    ],
    "4-3-3": [
        {y:92, x:50, l:1, o:1}, {y:75, x:15, l:2, o:1}, {y:75, x:38, l:2, o:2}, {y:75, x:62, l:2, o:3}, {y:75, x:85, l:2, o:4},
        {y:45, x:25, l:3, o:1}, {y:45, x:50, l:3, o:2}, {y:45, x:75, l:3, o:3}, {y:15, x:20, l:5, o:1}, {y:15, x:50, l:5, o:2}, {y:15, x:80, l:5, o:3}
    ],
    "3-5-2": [
        {y:92, x:50, l:1, o:1}, {y:75, x:25, l:2, o:1}, {y:75, x:50, l:2, o:2}, {y:75, x:75, l:2, o:3},
        {y:45, x:10, l:3, o:1}, {y:45, x:30, l:3, o:2}, {y:45, x:50, l:3, o:3}, {y:45, x:70, l:3, o:4}, {y:45, x:90, l:3, o:5}, {y:15, x:35, l:5, o:1}, {y:15, x:65, l:5, o:2}
    ]
};

function renderCampo() {
    const pitch = document.getElementById('pitch');
    pitch.innerHTML = '';
    const spots = schemi[moduloAttuale] || schemi["4-4-2"];
    spots.forEach(s => {
        const div = document.createElement('div');
        div.className = 'drop-spot';
        div.style.top = s.y + '%'; div.style.left = s.x + '%';
        div.dataset.l = s.l; div.dataset.o = s.o;
        div.ondragover = (e) => e.preventDefault();
        div.ondrop = onDrop;
        pitch.appendChild(div);
    });
    loadSavedPlayers();
}

function loadSavedPlayers() {
    const titolari = [
        <?php
        $resL = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo, fg.linea, fg.ordine_orizzontale FROM formazione_giocatori fg JOIN giocatori g ON fg.id_giocatore=g.id WHERE fg.id_partita=$1", [$id_partita]);
        while($r = pg_fetch_assoc($resL)) { echo "{id: {$r['id']}, nome: '" . addslashes($r['nome']) . "', ruolo: '{$r['ruolo']}', linea: {$r['linea']}, ordine: {$r['ordine_orizzontale']}},"; }
        ?>
    ];
    titolari.forEach(g => {
        const spot = document.querySelector(`.drop-spot[data-l="${g.linea}"][data-o="${g.ordine}"]`);
        if (spot) {
            const cognome = g.nome.split(' ').pop().toUpperCase();
            spot.innerHTML = `
                <div class="player-token" draggable="true" ondragstart="onDragStart(event, 'field')" data-id="${g.id}">
                    <button class="remove-btn" onclick="removePlayer(${g.id})">×</button>
                    <span class="name">${cognome}</span>
                    <span class="role">${g.ruolo}</span>
                </div>`;
        }
    });
}

function onDragStart(ev, source) {
    ev.dataTransfer.setData("id", ev.target.dataset.id);
    ev.dataTransfer.setData("source", source);
}

function onDrop(ev) {
    ev.preventDefault();
    const idTrascinato = ev.dataTransfer.getData("id");
    const source = ev.dataTransfer.getData("source");
    const spot = ev.currentTarget;
    const targetToken = spot.querySelector('.player-token');
    
    if (targetToken) {
        const idPresente = targetToken.dataset.id;
        if(idTrascinato == idPresente) return;
        if (source === 'field') {
            saveAjax('swap_players', { id_1: idTrascinato, id_2: idPresente, linea_target: spot.dataset.l, ordine_target: spot.dataset.o }).then(() => location.reload());
        }
    } else {
        saveAjax('save_pos', { id_g: idTrascinato, linea: spot.dataset.l, ordine: spot.dataset.o }).then(() => location.reload());
    }
}

function eseguiSostituzione() {
    const idEsce = document.getElementById('sub-esce').value;
    const idEntra = document.getElementById('sub-entra').value;
    if (!idEsce || !idEntra) { alert("Seleziona i giocatori."); return; }
    if (confirm("Registrare la sostituzione nel database?")) {
        saveAjax('make_sub', { id_esce: idEsce, id_entra: idEntra }).then(() => location.reload());
    }
}

function onDropBench(ev) {
    ev.preventDefault();
    const id = ev.dataTransfer.getData("id");
    saveAjax('add_bench', { id_g: id }).then(() => location.reload());
}

function deleteSub(idSub) {
    if(confirm("Eliminare il log?")) {
        saveAjax('delete_sub', { id_sub: idSub }).then(() => location.reload());
    }
}

function removePlayer(id) {
    saveAjax('remove', { id_g: id }).then(() => location.reload());
}

function cambiaModulo(v) {
    if(confirm("Reset formazione?")) {
        saveAjax('change_modulo', { modulo: v }).then(() => location.reload());
    }
}

function saveAjax(action, extra) {
    const params = new URLSearchParams({ ajax_action: action, id: id_partita = idPartita, ...extra });
    return fetch(window.location.href, { 
        method: 'POST', 
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
        body: params 
    });
}

document.addEventListener('DOMContentLoaded', renderCampo);
</script>