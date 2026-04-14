<?php
session_start();
require_once 'includes/db_connect.php'; 

//LOGICA PHP (POST) 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['testo_messaggio'], $_SESSION['id_utente'])) {
    $testo = trim($_POST['testo_messaggio']);
    if (!empty($testo)) {
        pg_query_params($db, "INSERT INTO public.messaggi (id_utente, testo) VALUES ($1, $2)", [$_SESSION['id_utente'], $testo]);
        header("Location: curva.php"); 
        exit;
    }
}

if (isset($_POST['elimina_messaggio'], $_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
    pg_query_params($db, "DELETE FROM public.messaggi WHERE id = $1", [(int)$_POST['id_messaggio']]);
    header("Location: curva.php"); 
    exit;
}

//RECUPERO MESSAGGI 
$messaggi = [];
if (isset($_SESSION['id_utente'])) {
    $res = pg_query($db, "SELECT m.id, m.testo, to_char(m.data_invio, 'DD/MM HH24:MI') as data_f, u.nome, u.ruolo 
                          FROM public.messaggi m JOIN public.utenti u ON m.id_utente = u.id 
                          ORDER BY m.data_invio DESC");
    if ($res) $messaggi = pg_fetch_all($res) ?: [];
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="css/styleCurva.css?v=1">

<main class="container-curva">

    <?php if (!isset($_SESSION['id_utente'])): ?>
        <div class="curva-header">
            <h1>La Curva 🐺</h1>
            <p>Il ruggito del Bianco-Verde</p>
        </div>
        <div class="guest-lock">
            <h2>🔒 Settore Riservato</h2>
            <p>Accedi per leggere i messaggi e partecipare alla bacheca.</p><br>
            <a href="login.php" class="btn-entra">Entra nello Stadio</a>
        </div>

    <?php else: ?>
        <div class="curva-header">
            <h1>Bacheca della Curva</h1>
            <p>Bentornato, <strong><?= htmlspecialchars($_SESSION['nome']) ?></strong>!</p>
        </div>

        <section class="write-box clearfix">
            <form action="curva.php" method="POST">
                <textarea name="testo_messaggio" rows="3" placeholder="Scrivi un coro..." required></textarea>
                <button type="submit" class="btn-grida">Grida in Curva 📣</button>
            </form>
        </section>

        <section class="feed">
            <?php if (empty($messaggi)): ?>
                <p class="empty-msg">Nessun messaggio presente... rompi il silenzio!</p>
            <?php else: ?>
                <?php foreach ($messaggi as $msg): ?>
                    <article class="msg-card">
                        <header class="msg-header">
                            <div class="user-info">
                                <div class="user-avatar"><?= strtoupper(substr($msg['nome'], 0, 1)) ?></div>
                                <div>
                                    <span class="user-name"><?= htmlspecialchars($msg['nome']) ?></span>
                                    <span class="badge-role <?= $msg['ruolo'] === 'admin' ? 'badge-admin' : 'badge-tifoso' ?>">
                                        <?= $msg['ruolo'] === 'admin' ? 'Admin' : 'Tifoso' ?>
                                    </span>
                                </div>
                            </div>
                            <span class="msg-time"><?= $msg['data_f'] ?></span>
                        </header>
                        
                        <div class="msg-body"><?= nl2br(htmlspecialchars($msg['testo'])) ?></div>
                        
                        <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                            <form action="curva.php" method="POST" class="form-delete">
                                <input type="hidden" name="id_messaggio" value="<?= $msg['id'] ?>">
                                <button type="submit" name="elimina_messaggio" class="btn-delete" onclick="return confirm('Rimuovere questo coro?')">🗑️ Elimina</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div class="curva-footer">
            <a href="login.php?logout=true" class="logout-link">Abbandona il settore (Logout)</a>
        </div>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>