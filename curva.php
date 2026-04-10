<?php
session_start();
require_once 'includes/db_connect.php'; 

// --- 1. LOGICA PHP (Invariata ma pulita) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['testo_messaggio'], $_SESSION['id_utente'])) {
    $testo = trim($_POST['testo_messaggio']);
    if (!empty($testo)) {
        pg_query_params($db, "INSERT INTO public.messaggi (id_utente, testo) VALUES ($1, $2)", [$_SESSION['id_utente'], $testo]);
        header("Location: curva.php"); exit;
    }
}

if (isset($_POST['elimina_messaggio'], $_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
    pg_query_params($db, "DELETE FROM public.messaggi WHERE id = $1", [(int)$_POST['id_messaggio']]);
    header("Location: curva.php"); exit;
}

$messaggi = [];
if (isset($_SESSION['id_utente'])) {
    $res = pg_query($db, "SELECT m.id, m.testo, to_char(m.data_invio, 'DD/MM HH24:MI') as data_f, u.nome, u.ruolo 
                          FROM public.messaggi m JOIN public.utenti u ON m.id_utente = u.id 
                          ORDER BY m.data_invio DESC");
    if ($res) $messaggi = pg_fetch_all($res) ?: [];
}

include 'includes/header.php';
?>

<style>
    :root {
        --verde-lupi: #2e7d32;
        --verde-light: #4caf50;
        --sfondo-dark: #121212;
        --card-bg: #ffffff;
    }

    .container-curva {
        max-width: 800px;
        margin: 20px auto;
        padding: 0 15px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Header Curva */
    .curva-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/stadium-blur.jpg'); /* Se hai un'immagine */
        background-size: cover;
        border-radius: 15px;
        margin-bottom: 30px;
        color: white;
    }

    .curva-header h1 { font-size: 2.5rem; text-transform: uppercase; letter-spacing: 2px; margin: 0; }
    .curva-header p { opacity: 0.9; font-style: italic; }

    /* Box Inserimento */
    .write-box {
        background: var(--card-bg);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 40px;
    }

    .write-box textarea {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        font-size: 1rem;
        resize: none;
        transition: border 0.3s;
    }

    .write-box textarea:focus { border-color: var(--verde-lupi); outline: none; }

    .btn-grida {
        background: var(--verde-lupi);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        float: right;
        margin-top: 10px;
        transition: 0.3s;
    }

    .btn-grida:hover { background: var(--verde-light); transform: translateY(-2px); }

    /* Card Messaggi */
    .msg-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .msg-card:hover { transform: scale(1.01); }

    .msg-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .user-info { display: flex; align-items: center; gap: 10px; }
    .user-avatar { 
        width: 40px; height: 40px; 
        background: #eee; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; color: var(--verde-lupi);
    }

    .user-name { font-weight: 700; color: #333; }
    .badge-role { 
        font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; 
        text-transform: uppercase; font-weight: bold;
    }
    .badge-admin { background: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; }
    .badge-tifoso { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

    .msg-time { font-size: 0.8rem; color: #888; }
    .msg-body { color: #444; line-height: 1.6; font-size: 1.05rem; white-space: pre-wrap; }

    .btn-delete {
        background: none; border: none; color: #ff5252;
        cursor: pointer; font-size: 0.8rem; opacity: 0.6;
        transition: 0.3s;
    }
    .btn-delete:hover { opacity: 1; }

    /* Area Ospiti */
    .guest-lock {
        text-align: center; background: rgba(255,255,255,0.1);
        padding: 50px; border-radius: 15px; border: 2px dashed rgba(255,255,255,0.3);
    }

    .clearfix::after { content: ""; clear: both; display: table; }
</style>

<main class="container-curva">

    <?php if (!isset($_SESSION['id_utente'])): ?>
        <div class="curva-header">
            <h1>La Curva 🐺</h1>
            <p>Il ruggito del Bianco-Verde</p>
        </div>
        <div class="guest-lock">
            <h2 style="color: black;">🔒 Settore Riservato</h2>
            <p style="color: #ddd; margin-bottom: 25px;">Solo i membri del branco possono leggere e scrivere in bacheca.</p>
            <a href="login.php" class="btn-primary" style="padding: 15px 40px; text-decoration: none; border-radius: 30px;">Entra nello Stadio</a>
        </div>

    <?php else: ?>
        <div class="curva-header">
            <h1>Bacheca della Curva</h1>
            <p>Bentornato, <strong><?= htmlspecialchars($_SESSION['nome']) ?></strong>! Fatti sentire.</p>
        </div>

        <section class="write-box clearfix">
            <form action="curva.php" method="POST">
                <textarea name="testo_messaggio" rows="3" placeholder="Scrivi un coro per i ragazzi..." required></textarea>
                <button type="submit" class="btn-grida">Grida in Curva 📣</button>
            </form>
        </section>

        <section class="feed">
            <?php if (empty($messaggi)): ?>
                <p style="text-align: center; color: white; opacity: 0.7;">Ancora nessun coro... rompi il silenzio!</p>
            <?php else: ?>
                <?php foreach ($messaggi as $msg): ?>
                    <article class="msg-card">
                        <header class="msg-header">
                            <div class="user-info">
                                <div class="user-avatar"><?= strtoupper(substr($msg['nome'], 0, 1)) ?></div>
                                <div>
                                    <span class="user-name"><?= htmlspecialchars($msg['nome']) ?></span>
                                    <span class="badge-role <?= $msg['ruolo'] === 'admin' ? 'badge-admin' : 'badge-tifoso' ?>">
                                        <?= $msg['ruolo'] === 'admin' ? '🛡️ Admin' : '⚽ Tifoso' ?>
                                    </span>
                                </div>
                            </div>
                            <span class="msg-time"><?= $msg['data_f'] ?></span>
                        </header>

                        <div class="msg-body"><?= nl2br(htmlspecialchars($msg['testo'])) ?></div>

                        <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                            <form action="curva.php" method="POST" style="text-align: right; margin-top: 10px;">
                                <input type="hidden" name="id_messaggio" value="<?= $msg['id'] ?>">
                                <button type="submit" name="elimina_messaggio" class="btn-delete" onclick="return confirm('Rimuovere questo coro?')">🗑️ Elimina messaggio</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <p style="text-align: center; margin-top: 40px;">
            <a href="login.php?logout=true" style="color: #ff5252; text-decoration: none; font-size: 0.9rem;">Abbandona il settore (Logout)</a>
        </p>

    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>