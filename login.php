<?php
session_start();
require_once 'includes/db_connect.php'; 

$errore_login = "";
$errore_reg = "";
$errore_recupero = "";
$msg_successo = "";

// --- LOGICA REGISTRAZIONE ---
if (isset($_POST['submit_registrazione'])) {
    $nome = htmlspecialchars($_POST['nome']);
    $email = htmlspecialchars($_POST['email']);
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT); 
    $giocatore = htmlspecialchars($_POST['giocatore']);

    $query_ins = "INSERT INTO public.utenti (nome, email, password, giocatore_preferito, ruolo) VALUES ($1, $2, $3, $4, 'tifoso')";
    $res = pg_query_params($db, $query_ins, array($nome, $email, $password_hash, $giocatore));

    if ($res) {
        $query_check = "SELECT id, nome, ruolo FROM public.utenti WHERE email = $1";
        $res_check = pg_query_params($db, $query_check, array($email));
        $utente = pg_fetch_assoc($res_check);

        $_SESSION['id_utente'] = $utente['id'];
        $_SESSION['nome'] = $utente['nome'];
        $_SESSION['ruolo'] = trim($utente['ruolo']);
        
        header("Location: index.php");
        exit;
    } else {
        $errore_reg = "Errore: l'email potrebbe essere già registrata.";
    }
}

// --- LOGICA RECUPERO PASSWORD ---
if (isset($_POST['submit_recupero'])) {
    $email = $_POST['email_recupero'];
    $giocatore_sicurezza = $_POST['giocatore_sicurezza'];
    $nuova_password = $_POST['nuova_password'];

    $query_check = "SELECT id, giocatore_preferito FROM public.utenti WHERE email = $1";
    $res_check = pg_query_params($db, $query_check, array($email));

    if ($res_check && pg_num_rows($res_check) > 0) {
        $utente = pg_fetch_assoc($res_check);
        if (strtolower(trim($utente['giocatore_preferito'])) === strtolower(trim($giocatore_sicurezza))) {
            $nuovo_hash = password_hash($nuova_password, PASSWORD_BCRYPT);
            $query_update = "UPDATE public.utenti SET password = $1 WHERE email = $2";
            $res_update = pg_query_params($db, $query_update, array($nuovo_hash, $email));
            if ($res_update) {
                $msg_successo = "Password aggiornata! Ora puoi accedere.";
            } else {
                $errore_recupero = "Errore durante l'aggiornamento.";
            }
        } else {
            $errore_recupero = "Risposta di sicurezza errata.";
        }
    } else {
        $errore_recupero = "Nessun account trovato con questa email.";
    }
}

// --- LOGICA LOGIN ---
if (isset($_POST['submit_login'])) {
    $email = $_POST['email'];
    $password_inserita = $_POST['password'];

    $query_login = "SELECT * FROM public.utenti WHERE email = $1";
    $res_login = pg_query_params($db, $query_login, array($email));
    
    if ($res_login) {
        $utente = pg_fetch_assoc($res_login);
        if ($utente && password_verify($password_inserita, $utente['password'])) {
            $_SESSION['id_utente'] = $utente['id'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['ruolo'] = trim($utente['ruolo']);

            $redirect = ($_SESSION['ruolo'] === 'admin') ? 'admin.php' : 'index.php';
            header("Location: $redirect"); 
            exit;
        } else {
            $errore_login = "Email o password errati!";
        }
    } else {
        $errore_login = "Errore di connessione al database.";
    }
}

// --- LOGICA LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

include 'includes/header.php';
?>

<?php 
    $css_path = 'css/style2.css';
    $version = file_exists($css_path) ? filemtime($css_path) : '1.0';
?>
<link rel="stylesheet" href="<?= $css_path ?>?v=<?= $version ?>">

<main class="container-login">
    <section class="portale-tifoso">
        <h2>Portale Lupo Biancoverde 🐺</h2>
        
        <div class="tab-buttons">
            <button id="btnLogin" class="tab-btn active" onclick="cambiaTab('login')">Accesso</button>
            <button id="btnRegistrati" class="tab-btn" onclick="cambiaTab('registrati')">Registrati</button>
        </div>

        <div id="tab-login" class="tab-content">
            <?php if($errore_login) echo "<p class='msg error'>$errore_login</p>"; ?>
            <?php if($msg_successo) echo "<p class='msg success'>$msg_successo</p>"; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="Inserisci la tua email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="submit_login" class="btn-submit btn-access">ENTRA</button>
            </form>
            <div class="text-center">
                <a href="#" onclick="cambiaTab('recupero'); return false;" class="link-alt">Hai dimenticato la password?</a>
            </div>
        </div>

        <div id="tab-registrati" class="tab-content" style="display:none;">
            <?php if($errore_reg) echo "<p class='msg error'>$errore_reg</p>"; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Nome Completo:</label>
                    <input type="text" name="nome" placeholder="es. Mario Rossi" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="email@esempio.it" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Scegli una password sicura" required>
                </div>
                <div class="form-group">
                    <label>Giocatore Preferito (Domanda di sicurezza):</label>
                    <input type="text" name="giocatore" placeholder="La tua risposta segreta" required>
                </div>
                <button type="submit" name="submit_registrazione" class="btn-submit btn-reg">CREA ACCOUNT</button>
            </form>
        </div>

        <div id="tab-recupero" class="tab-content" style="display:none;">
            <h3>Recupera Password</h3>
            <p class="info-text">Inserisci l'email e la risposta alla domanda di sicurezza.</p>
            
            <?php if($errore_recupero) echo "<p class='msg error'>$errore_recupero</p>"; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Tua Email:</label>
                    <input type="email" name="email_recupero" required>
                </div>
                <div class="form-group">
                    <label>Domanda: Qual è il tuo Giocatore Preferito?</label>
                    <input type="text" name="giocatore_sicurezza" required>
                </div>
                <div class="form-group">
                    <label>Scegli Nuova Password:</label>
                    <input type="password" name="nuova_password" required>
                </div>
                <button type="submit" name="submit_recupero" class="btn-submit btn-reset">AGGIORNA PASSWORD</button>
            </form>
            <div class="text-center">
                <a href="#" onclick="cambiaTab('login'); return false;" class="link-back">← Torna al Login</a>
            </div>
        </div>
    </section>
</main>

<script>
function cambiaTab(tipo) {
    const tabs = ['tab-login', 'tab-registrati', 'tab-recupero'];
    const btns = { 'login': 'btnLogin', 'registrati': 'btnRegistrati' };
    
    // Nascondi tutto
    tabs.forEach(tab => document.getElementById(tab).style.display = 'none');
    
    // Rimuovi active dai bottoni principali
    Object.values(btns).forEach(id => document.getElementById(id).classList.remove('active'));

    // Mostra il selezionato
    document.getElementById('tab-' + tipo).style.display = 'block';
    
    // Se è uno dei due bottoni principali, aggiungi active
    if (btns[tipo]) {
        document.getElementById(btns[tipo]).classList.add('active');
    }
}

// Mantieni il tab recupero se c'è un errore specifico
<?php if(isset($_POST['submit_recupero']) && !empty($errore_recupero)): ?>
    cambiaTab('recupero');
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>