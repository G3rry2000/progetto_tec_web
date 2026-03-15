<?php
session_start();
require_once 'includes/db_connect.php';

// 1. PROTEZIONE PAGINA
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 2. LOGICA AGGIORNAMENTO (Gol e Presenze)
if (isset($_GET['azione']) && isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = (int)$_GET['id'];
    $tipo = ($_GET['tipo'] === 'presenze') ? 'presenze' : 'gol';
    
    if ($_GET['azione'] === 'piu') {
        $sql = "UPDATE public.giocatori SET $tipo = $tipo + 1 WHERE id = $1";
    } elseif ($_GET['azione'] === 'meno') {
        $sql = "UPDATE public.giocatori SET $tipo = CASE WHEN $tipo > 0 THEN $tipo - 1 ELSE 0 END WHERE id = $1";
    }
    
    pg_query_params($db, $sql, array($id));
    header("Location: admin.php"); 
    exit;
}

// 3. RECUPERO DATI PER LE STATISTICHE
$res_stats = pg_query($db, "SELECT SUM(gol) as totale_gol, COUNT(*) as totale_rosa FROM giocatori");
$stats = pg_fetch_assoc($res_stats);

// 4. RECUPERO GIOCATORI
$res = pg_query($db, "SELECT * FROM giocatori ORDER BY ruolo DESC, gol DESC, nome ASC");
$giocatori = pg_fetch_all($res);

include 'includes/header.php';
?>

<style>
    :root { --verde-morra: #2d5a27; --grigio-fango: #333; }
    .admin-container { max-width: 1100px; margin: 40px auto; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 5px solid var(--verde-morra); text-align: center; }
    .stat-card h3 { margin: 0; font-size: 14px; color: #666; text-transform: uppercase; }
    .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: var(--grigio-fango); }
    
    .table-container { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    thead { background: var(--grigio-fango); color: white; }
    th { padding: 15px; text-align: left; }
    td { padding: 12px 15px; border-bottom: 1px solid #eee; }
    tr:hover { background-color: #f9f9f9; }
    
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; }
    .badge-ATT { background: #e74c3c; } .badge-CEN { background: #3498db; }
    .badge-DIF { background: #f1c40f; color: #333; } .badge-POR { background: #9b59b6; }
    
    .btn-action { text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; transition: 0.2s; display: inline-block; margin: 2px; }
    .btn-plus { background: #2ecc71; color: white; }
    .btn-minus { background: #ecf0f1; color: #7f8c8d; }
    .btn-plus:hover { background: #27ae60; }
</style>

<div class="admin-container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="margin:0; color: var(--grigio-fango);">Gestione Squadra 🐺</h1>
            <p style="color: #666;">Benvenuto, <strong><?php echo $_SESSION['nome']; ?></strong>. Qui gestisci la rosa dell'ASD Morra.</p>
        </div>
        <a href="login.php?logout=1" class="btn-action" style="background: #e74c3c; color:white; padding: 10px 20px;">Disconnetti</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Giocatori in Rosa</h3>
            <p><?php echo $stats['totale_rosa']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Gol Totali Segnati</h3>
            <p>⚽ <?php echo $stats['totale_gol']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Modalità</h3>
            <p>AMMINISTRATORE</p>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Giocatore</th>
                    <th>Ruolo</th>
                    <th style="text-align:center;">Presenze</th>
                    <th style="text-align:center;">Gol</th>
                    <th style="text-align:right;">Azioni Rapide</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($giocatori as $g): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($g['nome']); ?></strong></td>
                    <td><span class="badge badge-<?php echo $g['ruolo']; ?>"><?php echo $g['ruolo']; ?></span></td>
                    
                    <td style="text-align:center;">
                        <span style="font-size: 1.1em;"><?php echo $g['presenze']; ?></span>
                    </td>
                    
                    <td style="text-align:center;">
                        <span style="font-size: 1.1em; font-weight:bold; color: var(--verde-morra);">
                            <?php echo $g['gol']; ?> ⚽
                        </span>
                    </td>

                    <td style="text-align:right;">
                        <div style="margin-bottom: 5px;">
                            <small style="color:#999; margin-right:5px;">Gol:</small>
                            <a href="admin.php?azione=piu&tipo=gol&id=<?php echo $g['id']; ?>" class="btn-action btn-plus">+</a>
                            <a href="admin.php?azione=meno&tipo=gol&id=<?php echo $g['id']; ?>" class="btn-action btn-minus">-</a>
                        </div>
                        <div>
                            <small style="color:#999; margin-right:5px;">Presenze:</small>
                            <a href="admin.php?azione=piu&tipo=presenze&id=<?php echo $g['id']; ?>" class="btn-action btn-plus" style="background:#34495e;">+</a>
                            <a href="admin.php?azione=meno&tipo=presenze&id=<?php echo $g['id']; ?>" class="btn-action btn-minus">-</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <p style="color: #888;">ASD Morra De Sanctis - Portale Gestionale v1.0</p>
    </div>

</div>

<?php include 'includes/footer.php'; ?>