<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') die("Negato");

$msg = "";

// --- 1. LOGICA INSERIMENTO NUOVO GIOCATORE ---
if (isset($_POST['add_player'])) {
    $nome = trim($_POST['nome_nuovo']);
    $nascita = trim($_POST['nascita_nuovo']);
    $ruolo = $_POST['ruolo_nuovo'];

    if (!empty($nome) && !empty($ruolo)) {
        $query_ins = "INSERT INTO public.giocatori (nome, nascita, ruolo, gol, presenze, ammonizioni, espulsioni) 
                      VALUES ($1, $2, $3, 0, 0, 0, 0)";
        $res_ins = pg_query_params($db, $query_ins, [$nome, $nascita, $ruolo]);
        
        if ($res_ins) {
            $msg = "Nuovo lupo aggiunto alla muta! 🐺";
        } else {
            $msg = "Errore durante l'inserimento.";
        }
    } else {
        $msg = "Nome e Ruolo sono obbligatori.";
    }
}

// --- 2. LOGICA SALVATAGGIO MASSIVO STATISTICHE ---
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

// Recupero la rosa aggiornata
$resG = pg_query($db, "SELECT * FROM giocatori ORDER BY 
    CASE ruolo WHEN 'POR' THEN 1 WHEN 'DIF' THEN 2 WHEN 'CEN' THEN 3 WHEN 'ATT' THEN 4 ELSE 5 END, 
    nome ASC");

include 'includes/header.php';
?>

<style>
    .admin-stats-container { max-width: 1000px; margin: 40px auto; padding: 20px; background: #161b22; border-radius: 10px; font-family: sans-serif; }
    .msg-box { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; background: #2e7d32; color: white; }
    
    /* Form Nuovo Giocatore */
    .add-player-box { background: #0d1117; padding: 20px; border-radius: 8px; border: 1px solid #30363d; margin-bottom: 30px; }
    .add-player-box h3 { margin-top: 0; color: #4caf50; }
    .form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 0.8rem; color: #8b949e; }
    .form-group input, .form-group select { padding: 8px; background: #161b22; border: 1px solid #30363d; color: white; border-radius: 4px; }
    
    /* Tabella Statistiche */
    .stats-table { width: 100%; color: white; border-collapse: collapse; margin-top: 10px; }
    .stats-table th { border-bottom: 2px solid #30363d; padding: 10px; text-allign: left; color: #8b949e; }
    .stats-table td { padding: 10px; border-bottom: 1px solid #30363d; }
    .stats-table input[type="number"] { width: 50px; background: #0d1117; color: white; border: 1px solid #333; padding: 5px; border-radius: 3px; }
    
    .btn-save { background: #2e7d32; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 20px; }
    .btn-add { background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
</style>

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
                    <th style="text-align:left;">Giocatore</th>
                    <th>Presenze</th>
                    <th>Gol</th>
                    <th>🟨</th>
                    <th>🟥</th>
                </tr>
            </thead>
            <tbody>
                <?php while($g = pg_fetch_assoc($resG)): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($g['nome']) ?></strong><br>
                        <small style="color:#4caf50"><?= $g['ruolo'] ?></small>
                    </td>
                    <td style="text-align:center;">
                        <input type="number" name="stat[<?= $g['id'] ?>][p]" value="<?= $g['presenze'] ?>" min="0">
                    </td>
                    <td style="text-align:center;">
                        <input type="number" name="stat[<?= $g['id'] ?>][g]" value="<?= $g['gol'] ?>" min="0">
                    </td>
                    <td style="text-align:center;">
                        <input type="number" name="stat[<?= $g['id'] ?>][am]" value="<?= $g['ammonizioni'] ?>" min="0">
                    </td>
                    <td style="text-align:center;">
                        <input type="number" name="stat[<?= $g['id'] ?>][es]" value="<?= $g['espulsioni'] ?>" min="0">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: flex; align-items: center; justify-content: space-between;">
            <button type="submit" name="save_stats" class="btn-save">💾 Salva Tutte le Statistiche</button>
            <a href="admin.php" style="color:#8b949e; text-decoration: none;">← Torna alla Dashboard</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>