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
        $_SESSION['ruolo'] = $utente['ruolo'];
        
        header("Location: index.php");
        exit;
    } else {
        $errore_reg = "Errore: l'email potrebbe essere già registrata.";
    }
}

// --- LOGICA RECUPERO PASSWORD (NUOVA) ---
if (isset($_POST['submit_recupero'])) {
    $email = $_POST['email_recupero'];
    $giocatore_sicurezza = $_POST['giocatore_sicurezza'];
    $nuova_password = $_POST['nuova_password'];

    // Cerchiamo l'utente tramite email
    $query_check = "SELECT id, giocatore_preferito FROM public.utenti WHERE email = $1";
    $res_check = pg_query_params($db, $query_check, array($email));

    if ($res_check && pg_num_rows($res_check) > 0) {
        $utente = pg_fetch_assoc($res_check);
        
        // Controlliamo se la risposta di sicurezza coincide (ignorando maiuscole/minuscole e spazi)
        if (strtolower(trim($utente['giocatore_preferito'])) === strtolower(trim($giocatore_sicurezza))) {
            $nuovo_hash = password_hash($nuova_password, PASSWORD_BCRYPT);
            
            $query_update = "UPDATE public.utenti SET password = $1 WHERE email = $2";
            $res_update = pg_query_params($db, $query_update, array($nuovo_hash, $email));
            
            if ($res_update) {
                $msg_successo = "Password aggiornata con successo! Ora puoi accedere.";
            } else {
                $errore_recupero = "Errore durante l'aggiornamento della password.";
            }
        } else {
            $errore_recupero = "Risposta di sicurezza errata. Giocatore preferito non corrispondente.";
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

            if ($_SESSION['ruolo'] === 'admin') {
                header("Location: admin.php"); 
            } else {
                header("Location: index.php"); 
            }
            exit;
        } else {
            $errore_login = "Email o password errati, riprova!";
        }
    } else {
        $errore_login = "Errore nella comunicazione con il database.";
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

<main class="container-login" style="max-width: 500px; margin: 50px auto; padding: 20px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-family: sans-serif;">
    <section class="portale-tifoso">
        <h2 style="text-align: center; color: green;">Portale Lupo Biancoverde 🐺</h2>
        
        <div class="tab-buttons" style="display: flex; justify-content: space-around; margin-bottom: 20px;">
            <button id="btnLogin" onclick="cambiaTab('login')" style="cursor:pointer; padding: 10px; border:none; background:none; border-bottom: 2px solid green; font-weight:bold;">Accesso</button>
            <button id="btnRegistrati" onclick="cambiaTab('registrati')" style="cursor:pointer; padding: 10px; border:none; background:none; font-weight:bold;">Registrati</button>
        </div>

        <div id="tab-login">
            <?php if($errore_login) echo "<p style='color:red; text-align:center;'>$errore_login</p>"; ?>
            <?php if($msg_successo) echo "<p style='color:green; text-align:center; font-weight:bold;'>$msg_successo</p>"; ?>
            <form action="login.php" method="POST">
                <div style="margin-bottom:15px;">
                    <label>Email:</label><br>
                    <input type="email" name="email" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Password:</label><br>
                    <input type="password" name="password" required style="width:95%; padding:8px;">
                </div>
                <button type="submit" name="submit_login" style="width:100%; padding:10px; background:green; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">ENTRA</button>
            </form>
            <p style="text-align: center; margin-top: 15px;">
                <a href="#" onclick="cambiaTab('recupero'); return false;" style="color: #555; font-size: 0.9em; text-decoration: underline;">Password dimenticata?</a>
            </p>
        </div>

        <div id="tab-registrati" style="display:none;">
            <?php if($errore_reg) echo "<p style='color:red; text-align:center;'>$errore_reg</p>"; ?>
            <form action="login.php" method="POST">
                <div style="margin-bottom:10px;">
                    <label>Nome:</label><br>
                    <input type="text" name="nome" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:10px;">
                    <label>Email:</label><br>
                    <input type="email" name="email" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:10px;">
                    <label>Password:</label><br>
                    <input type="password" name="password" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:10px;">
                    <label>Giocatore Preferito (Usato per il reset della password):</label><br>
                    <input type="text" name="giocatore" required style="width:95%; padding:8px;">
                </div>
                <button type="submit" name="submit_registrazione" style="width:100%; padding:10px; background:#333; color:white; border:none; border-radius:5px; cursor:pointer;">REGISTRATI</button>
            </form>
        </div>

        <div id="tab-recupero" style="display:none;">
            <h3 style="text-align:center; color:#333;">Recupera Password</h3>
            <p style="font-size:0.85em; color:#666; text-align:center;">Rispondi alla domanda di sicurezza per creare una nuova password.</p>
            
            <?php if($errore_recupero) echo "<p style='color:red; text-align:center;'>$errore_recupero</p>"; ?>
            
            <form action="login.php" method="POST">
                <div style="margin-bottom:10px;">
                    <label>La tua Email:</label><br>
                    <input type="email" name="email_recupero" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:10px;">
                    <label>Domanda: Qual è il tuo Giocatore Preferito?</label><br>
                    <input type="text" name="giocatore_sicurezza" required style="width:95%; padding:8px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>Nuova Password:</label><br>
                    <input type="password" name="nuova_password" required style="width:95%; padding:8px;">
                </div>
                <button type="submit" name="submit_recupero" style="width:100%; padding:10px; background:#ff9800; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">REIMPOSTA PASSWORD</button>
            </form>
            <p style="text-align: center; margin-top: 15px;">
                <a href="#" onclick="cambiaTab('login'); return false;" style="color: green; font-size: 0.9em;">← Torna al Login</a>
            </p>
        </div>
    </section>
</main>

<script>
function cambiaTab(tipo) {
    const L = document.getElementById('tab-login');
    const R = document.getElementById('tab-registrati');
    const P = document.getElementById('tab-recupero');
    const btnL = document.getElementById('btnLogin');
    const btnR = document.getElementById('btnRegistrati');
    
    // Nascondiamo tutto di default
    L.style.display = 'none';
    R.style.display = 'none';
    P.style.display = 'none';
    
    // Resettiamo le linee verdi sui bottoni
    btnL.style.borderBottom = 'none';
    btnR.style.borderBottom = 'none';

    if (tipo === 'login') {
        L.style.display = 'block';
        btnL.style.borderBottom = '2px solid green';
    } else if (tipo === 'registrati') {
        R.style.display = 'block';
        btnR.style.borderBottom = '2px solid green';
    } else if (tipo === 'recupero') {
        P.style.display = 'block';
        // Non evidenziamo i bottoni sopra per far capire che è in una schermata speciale
    }
}

// Se c'è un errore nel recupero, mantieni il tab aperto dopo il ricaricamento
<?php if(isset($_POST['submit_recupero']) && !empty($errore_recupero)): ?>
    cambiaTab('recupero');
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>