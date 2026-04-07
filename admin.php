<?php
session_start();
require_once 'includes/db_connect.php';

// Controllo Accesso Globale
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    die("Accesso Negato");
}

// Determina quale pagina mostrare (default: dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ==========================================
// 1. GESTIONE LOGICA E RICHIESTE (POST/AJAX)
// ==========================================

if ($page === 'classifica') {
    $msg_classifica = ""; // Variabile per i messaggi di conferma

    // Aggiornamento Classifica
    if (isset($_POST['update_classifica'])) {
        foreach ($_POST['team'] as $id => $v) {
            pg_query_params($db, "UPDATE classifica SET pt=$1, g=$2, v=$3, n=$4, p=$5, dr=$6 WHERE id=$7",
                [$v['pt'], $v['g'], $v['v'], $v['n'], $v['p'], $v['dr'], $id]);
        }
        $msg_classifica = "Classifica aggiornata con successo! 🐺";
    }

    // Aggiornamento Risultato Partita
    if (isset($_POST['update_partita'])) {
        $id_partita_da_aggiornare = (int)$_POST['id_partita'];
        $gol_casa = (int)$_POST['gol_casa'];
        $gol_ospite = (int)$_POST['gol_ospite'];

        // Aggiorniamo i gol e settiamo la partita come giocata (giocata = true)
        $query_match = "UPDATE partite SET gol_casa = $1, gol_ospite = $2, giocata = true WHERE id = $3";
        if (pg_query_params($db, $query_match, [$gol_casa, $gol_ospite, $id_partita_da_aggiornare])) {
            $msg_classifica = "Risultato della partita registrato! ⚽";
        } else {
            $msg_classifica = "Errore durante il salvataggio del risultato.";
        }
    }

    // Recupero dati per le tabelle
    $resC = pg_query($db, "SELECT * FROM classifica ORDER BY pt DESC");
    $resPartite = pg_query($db, "SELECT id, giornata, casa, ospite, gol_casa, gol_ospite, giocata FROM partite ORDER BY giornata ASC");

} elseif ($page === 'formazione') {
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
        echo json_encode(['status' => 'ok']); 
        exit; // Ferma l'esecuzione per le chiamate AJAX
    }

} elseif ($page === 'rosa') {
    $msg = "";
    if (isset($_POST['add_player'])) {
        $nome = trim($_POST['nome_nuovo']);
        $nascita = trim($_POST['nascita_nuovo']);
        $ruolo = $_POST['ruolo_nuovo'];

        if (!empty($nome) && !empty($ruolo)) {
            $query_ins = "INSERT INTO public.giocatori (nome, nascita, ruolo, gol, presenze, ammonizioni, espulsioni) 
                          VALUES ($1, $2, $3, 0, 0, 0, 0)";
            $res_ins = pg_query_params($db, $query_ins, [$nome, $nascita, $ruolo]);
            $msg = $res_ins ? "Nuovo lupo aggiunto alla muta! 🐺" : "Errore durante l'inserimento.";
        } else {
            $msg = "Nome e Ruolo sono obbligatori.";
        }
    }

    if (isset($_POST['save_stats'])) {
        foreach ($_POST['stat'] as $id_g => $valori) {
            pg_query_params($db, "UPDATE giocatori SET 
                presenze = $1, gol = $2, ammonizioni = $3, espulsioni = $4 
                WHERE id = $5", 
                [(int)$valori['p'], (int)$valori['g'], (int)$valori['am'], (int)$valori['es'], $id_g]
            );
        }
        $msg = "Statistiche aggiornate con successo!";
    }

    $resG = pg_query($db, "SELECT * FROM giocatori ORDER BY 
        CASE ruolo WHEN 'POR' THEN 1 WHEN 'DIF' THEN 2 WHEN 'CEN' THEN 3 WHEN 'ATT' THEN 4 ELSE 5 END, 
        nome ASC");
}

// ==========================================
// 2. OUTPUT HTML
// ==========================================

$css_extra = 'styleAdmin.css'; // Carichiamo il CSS specifico dell'admin
include 'includes/header.php';

if ($page === 'dashboard'): 
?>

    <div style="text-align:center; margin-top:40px;">
        <h1 style="color:white;">Pannello di Controllo Admin 🐺</h1>
        <p style="color:#8b949e;">Benvenuto Comandante, cosa vogliamo gestire oggi?</p>
    </div>

    <div class="admin-dashboard">
        <a href="?page=formazione" class="admin-card">
            <i>🏟️</i><h2>Gestione Formazione</h2>
            <p>Scegli la giornata e schiera i titolari sul campo da gioco.</p>
        </a>
        <a href="?page=rosa" class="admin-card">
            <i>👥</i><h2>Gestione Rosa</h2>
            <p>Aggiorna gol, presenze e cartellini dei tuoi lupi.</p>
        </a>
        <a href="?page=classifica" class="admin-card">
            <i>🏆</i><h2>Modifica Classifica</h2>
            <p>Aggiorna i punti e le statistiche delle squadre del girone.</p>
        </a>
    </div>

<?php elseif ($page === 'classifica'): ?>
    
    <?php if(!empty($msg_classifica)): ?>
        <div style="max-width: 900px; margin: 20px auto; padding: 15px; background: rgba(76, 175, 80, 0.2); border: 1px solid #4caf50; color: #4caf50; border-radius: 5px; text-align: center;">
            <?= $msg_classifica ?>
        </div>
    <?php endif; ?>

    <div style="max-width: 900px; margin: 20px auto 40px auto; padding: 20px; background: #161b22; border-radius: 10px;">
        <h2 style="color:white;">Modifica Manuale Classifica</h2>
        <form method="POST">
            <table style="width:100%; color:white; text-align:left;">
                <tr>
                    <th>Squadra</th><th>PT</th><th>G</th><th>V</th><th>N</th><th>P</th><th>DR</th>
                </tr>
                <?php while($c = pg_fetch_assoc($resC)): ?>
                <tr>
                    <td><?= htmlspecialchars($c['squadra']) ?></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][pt]" value="<?= $c['pt'] ?>" style="width:40px;"></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][g]" value="<?= $c['g'] ?>" style="width:40px;"></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][v]" value="<?= $c['v'] ?>" style="width:40px;"></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][n]" value="<?= $c['n'] ?>" style="width:40px;"></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][p]" value="<?= $c['p'] ?>" style="width:40px;"></td>
                    <td><input type="number" name="team[<?= $c['id'] ?>][dr]" value="<?= $c['dr'] ?>" style="width:40px;"></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top:15px;">
                <button type="submit" name="update_classifica" style="background:#2e7d32; color:white; padding:10px; border:none; border-radius: 5px; cursor: pointer;">Aggiorna Classifica</button>
                <a href="?page=dashboard" style="color:#8b949e; text-decoration: none;">← Torna alla Dashboard</a>
            </div>
        </form>
    </div>

    <div style="max-width: 900px; margin: 0 auto 40px auto; padding: 20px; background: #161b22; border-radius: 10px; border-top: 3px solid #005cc5;">
        <h2 style="color:white; margin-bottom: 15px;">⚽ Inserisci Risultato Partita</h2>
        <form method="POST" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            
            <select name="id_partita" required style="padding: 10px; background: #0d1117; color: white; border: 1px solid #333; border-radius: 5px; flex-grow: 1;">
                <option value="">-- Seleziona la partita da aggiornare --</option>
                <?php while($p = pg_fetch_assoc($resPartite)): 
                    $giocata_testo = ($p['giocata'] == 't') ? " [GIA' GIOCATA: {$p['gol_casa']}-{$p['gol_ospite']}]" : "";
                ?>
                    <option value="<?= $p['id'] ?>">
                        Giornata <?= $p['giornata'] ?>: <?= htmlspecialchars($p['casa']) ?> vs <?= htmlspecialchars($p['ospite']) ?> <?= $giocata_testo ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div style="display: flex; align-items: center; gap: 5px; color: white;">
                <input type="number" name="gol_casa" placeholder="Gol Casa" min="0" required style="width: 80px; padding: 10px; background: #0d1117; color: white; border: 1px solid #333; border-radius: 5px;">
                <span>-</span>
                <input type="number" name="gol_ospite" placeholder="Gol Ospite" min="0" required style="width: 80px; padding: 10px; background: #0d1117; color: white; border: 1px solid #333; border-radius: 5px;">
            </div>

            <button type="submit" name="update_partita" style="background: #005cc5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                Salva Risultato
            </button>
        </form>
    </div>

<?php elseif ($page === 'formazione'): ?>

    <div class="admin-layout">
        <aside class="sidebar">
            <a href="?page=dashboard" style="color:#8b949e; text-decoration: none; display:block; margin-bottom:15px;">← Dashboard</a>
            <h3>Giornata</h3>
            <select onchange="location.href='?page=formazione&id='+this.value" style="width:100%; padding:8px; margin-bottom:20px; background:#0d1117; color:white;">
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
                        <b style='color:#4caf50'>IN:</b> <?= htmlspecialchars($s['entra']) ?> <br> 
                        <b style='color:#f44336'>OUT:</b> <?= htmlspecialchars($s['esce']) ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </aside>
    </div>

    <script>
    const idPartita = <?= $id_partita ?>;
    let moduloAttuale = "<?= $modulo_attuale ?>";
    const titolariInCampo = [
        <?php
        $resL = pg_query_params($db, "SELECT g.id, g.nome, g.ruolo, fg.linea, fg.ordine_orizzontale FROM formazione_giocatori fg JOIN giocatori g ON fg.id_giocatore=g.id WHERE fg.id_partita=$1", [$id_partita]);
        while($r = pg_fetch_assoc($resL)) { 
            echo "{id: {$r['id']}, nome: '" . addslashes($r['nome']) . "', ruolo: '{$r['ruolo']}', linea: {$r['linea']}, ordine: {$r['ordine_orizzontale']}},"; 
        }
        ?>
    ];
    </script>
    <script src="js/script.js"></script>

<?php elseif ($page === 'rosa'): ?>

    <div class="admin-stats-container">
        <h2 style="color:white; margin-bottom: 5px;">Gestione Rosa 🐺</h2>
        <p style="color: #8b949e; margin-bottom: 25px;">Aggiungi nuovi giocatori o aggiorna le statistiche stagionali.</p>

        <?php if(!empty($msg)) echo "<div class='msg-box'>$msg</div>"; ?>

        <div class="add-player-box">
            <h3>➕ Nuovo Giocatore</h3>
            <form method="POST" class="form-row">
                <div class="form-group">
                    <label>Nome e Cognome</label>
                    <input type="text" name="nome_nuovo" placeholder="Es. Davide Caputo" required>
                </div>
                <div class="form-group">
                    <label>Data di Nascita</label>
                    <input type="text" name="nascita_nuovo" placeholder="GG-MM-AAAA">
                </div>
                <div class="form-group">
                    <label>Ruolo</label>
                    <select name="ruolo_nuovo" required>
                        <option value="POR">Portiere (POR)</option>
                        <option value="DIF">Difensore (DIF)</option>
                        <option value="CEN" selected>Centrocampista (CEN)</option>
                        <option value="ATT">Attaccante (ATT)</option>
                    </select>
                </div>
                <button type="submit" name="add_player" class="btn-add">Aggiungi</button>
            </form>
        </div>

        <form method="POST">
            <h3 style="color:white; margin-bottom: 15px;">📊 Statistiche Stagionali</h3>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th style="text-align:left;">Giocatore</th><th>Presenze</th><th>Gol</th><th>🟨</th><th>🟥</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($g = pg_fetch_assoc($resG)): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($g['nome']) ?></strong><br>
                            <small style="color:#4caf50"><?= $g['ruolo'] ?></small>
                        </td>
                        <td style="text-align:center;"><input type="number" name="stat[<?= $g['id'] ?>][p]" value="<?= $g['presenze'] ?>" min="0"></td>
                        <td style="text-align:center;"><input type="number" name="stat[<?= $g['id'] ?>][g]" value="<?= $g['gol'] ?>" min="0"></td>
                        <td style="text-align:center;"><input type="number" name="stat[<?= $g['id'] ?>][am]" value="<?= $g['ammonizioni'] ?>" min="0"></td>
                        <td style="text-align:center;"><input type="number" name="stat[<?= $g['id'] ?>][es]" value="<?= $g['espulsioni'] ?>" min="0"></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <button type="submit" name="save_stats" class="btn-save">💾 Salva Tutte le Statistiche</button>
                <a href="?page=dashboard" style="color:#8b949e; text-decoration: none;">← Torna alla Dashboard</a>
            </div>
        </form>
    </div>

<?php endif; ?>
<?php include 'includes/footer.php'; ?>