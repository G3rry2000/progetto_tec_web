<?php
session_start();
require_once 'includes/db_connect.php'; 

$errore_login = "";
$errore_reg = "";

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
        
        header("Location: index.php"); // Dopo registrazione va in home
        exit;
    } else {
        $errore_reg = "Errore: l'email potrebbe essere già registrata.";
    }
}

// --- LOGICA LOGIN ---
if (isset($_POST['submit_login'])) {
    $email = $_POST['email'];
    $password_inserita = $_POST['password'];

    // Specifichiamo public.utenti per sicurezza
    $query_login = "SELECT * FROM public.utenti WHERE email = $1";
    $res_login = pg_query_params($db, $query_login, array($email));
    
    if ($res_login) {
        $utente = pg_fetch_assoc($res_login);

        // Confronto password inserita con hash nel DB
        if ($utente && password_verify($password_inserita, $utente['password'])) {
            $_SESSION['id_utente'] = $utente['id'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['ruolo'] = trim($utente['ruolo']); // trim rimuove spazi vuoti accidentali

            // REINDIRIZZAMENTO DIFFERENZIATO
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
                    <label>Giocatore Preferito:</label><br>
                    <input type="text" name="giocatore" required style="width:95%; padding:8px;">
                </div>
                <button type="submit" name="submit_registrazione" style="width:100%; padding:10px; background:#333; color:white; border:none; border-radius:5px; cursor:pointer;">REGISTRATI</button>
            </form>
        </div>
    </section>
</main>

<script>
function cambiaTab(tipo) {
    const L = document.getElementById('tab-login');
    const R = document.getElementById('tab-registrati');
    const btnL = document.getElementById('btnLogin');
    const btnR = document.getElementById('btnRegistrati');
    if (tipo === 'login') {
        L.style.display = 'block'; R.style.display = 'none';
        btnL.style.borderBottom = '2px solid green'; btnR.style.borderBottom = 'none';
    } else {
        L.style.display = 'none'; R.style.display = 'block';
        btnR.style.borderBottom = '2px solid green'; btnL.style.borderBottom = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>