<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    die("Accesso negato.");
}

include 'includes/header.php';
?>

<style>
    .admin-dashboard {
        max-width: 1200px;
        margin: 50px auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        padding: 20px;
    }
    .admin-card {
        background: #161b22;
        border: 1px solid #30363d;
        border-radius: 15px;
        padding: 40px 20px;
        text-align: center;
        transition: 0.3s;
        text-decoration: none;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .admin-card:hover {
        transform: translateY(-10px);
        border-color: #2e7d32;
        background: #1c2128;
    }
    .admin-card i { font-size: 50px; margin-bottom: 20px; color: #4caf50; }
    .admin-card h2 { margin: 10px 0; font-size: 1.5rem; }
    .admin-card p { color: #8b949e; font-size: 0.9rem; }
</style>

<div style="text-align:center; margin-top:40px;">
    <h1 style="color:white;">Pannello di Controllo Admin 🐺</h1>
    <p style="color:#8b949e;">Benvenuto Comandante, cosa vogliamo gestire oggi?</p>
</div>

<div class="admin-dashboard">
    <a href="admin_formazione.php" class="admin-card">
        <i>🏟️</i>
        <h2>Gestione Formazione</h2>
        <p>Scegli la giornata e schiera i titolari sul campo da gioco.</p>
    </a>

    <a href="admin_rosa.php" class="admin-card">
        <i>👥</i>
        <h2>Gestione Rosa</h2>
        <p>Aggiorna gol, presenze e cartellini dei tuoi lupi.</p>
    </a>

    <a href="admin_classifica.php" class="admin-card">
        <i>🏆</i>
        <h2>Modifica Classifica</h2>
        <p>Aggiorna i punti e le statistiche delle squadre del girone.</p>
    </a>
</div>

<?php include 'includes/footer.php'; ?>