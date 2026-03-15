<?php
session_start();
// Assicurati che il percorso sia corretto (nel tuo precedente messaggio era db_connect.php)
require_once 'includes/db_connect.php'; 

$errore_login = "";
$errore_reg = "";

// --- LOGICA REGISTRAZIONE ---
if (isset($_POST['submit_registrazione'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    // Hash della password per sicurezza (richiesto dai buoni standard di progetto)
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT); 
    $giocatore = $_POST['giocatore'];

    // 1. Prepariamo la query di inserimento (Previene SQL Injection)
    $query_ins = "INSERT INTO utenti (nome, email, password, giocatore_preferito, ruolo) VALUES ($1, $2, $3, $4, 'tifoso')";
    $prep = pg_prepare($db, "insert_user", $query_ins);

    if ($prep) {
        // 2. Eseguiamo la query
        $res = pg_execute($db, "insert_user", array($nome, $email, $password_hash, $giocatore));

        if ($res) {
            // Recuperiamo l'utente appena creato per loggarlo
            $query_check = pg_prepare($db, "check_after_reg", "SELECT id, nome, ruolo FROM utenti WHERE email = $1");
            $res_check = pg_execute($db, "check_after_reg", array($email));
            $utente = pg_fetch_assoc($res_check);

            $_SESSION['id_utente'] = $utente['id'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            
            header("Location: curva.php");
            exit;
        } else {
            // Errore 23505 in Postgres significa "chiave duplicata" (email già esistente)
            $errore_reg = "Errore: l'email potrebbe essere già registrata o i dati non sono validi.";
        }
    }
}

// --- LOGICA LOGIN ---
if (isset($_POST['submit_login'])) {
    $email = $_POST['email'];
    $password_inserita = $_POST['password'];

    // 1. Prepariamo la query di selezione
    $query_login = "SELECT * FROM utenti WHERE email = $1";
    pg_prepare($db, "select_user", $query_login);
    
    // 2. Eseguiamo
    $res_login = pg_execute($db, "select_user", array($email));
    $utente = pg_fetch_assoc($res_login);

    // 3. Verifichiamo se l'utente esiste e se la password coincide con l'hash nel DB
    if ($utente && password_verify($password_inserita, $utente['password'])) {
        $_SESSION['id_utente'] = $utente['id'];
        $_SESSION['nome'] = $utente['nome'];
        $_SESSION['ruolo'] = $utente['ruolo'];
        header("Location: curva.php");
        exit;
    } else {
        $errore_login = "Email o password errati, riprova!";
    }
}

// --- LOGICA LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Inclusione Header
$css_extra = "css/style2.css";
include 'includes/header.php';
?>

<main class="container-login">
    <section class="portale-tifoso">
        <h2>Portale del Tifoso</h2>
        
        <div class="tab-buttons">
            <button id="btnLogin" class="tab-btn active" onclick="cambiaTab('login')">Accesso</button>
            <button id="btnRegistrati" class="tab-btn" onclick="cambiaTab('registrati')">Unisciti alla Squadra</button>
        </div>

        <div id="tab-login" class="tab-content active">
            <?php if(!empty($errore_login)) { echo "<p class='errore' style='color:red;'>$errore_login</p>"; } ?>
            
            <form id="formLogin" action="login.php" method="POST">
                <div class="form-group">
                    <label for="login-email">Email:</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                
                <button type="submit" name="submit_login" class="btn-primary">Entra</button>
            </form>
        </div>

        <div id="tab-registrati" class="tab-content" style="display:none;">
            <?php if(!empty($errore_reg)) { echo "<p class='errore' style='color:red;'>$errore_reg</p>"; } ?>
            
            <form id="formRegister" action="login.php" method="POST">
                <div class="form-group">
                    <label for="reg-nome">Nome:</label>
                    <input type="text" id="reg-nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-email">Email:</label>
                    <input type="email" id="reg-email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-password">Password:</label>
                    <input type="password" id="reg-password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-giocatore">Giocatore preferito:</label>
                    <input type="text" id="reg-giocatore" name="giocatore" value="<?php echo isset($_POST['giocatore']) ? htmlspecialchars($_POST['giocatore']) : ''; ?>" required>
                </div>
                
                <button type="submit" name="submit_registrazione" class="btn-primary">Registrati</button>
            </form>
        </div>
    </section>
</main>

<script>
// Funzione semplice per cambiare tab senza caricare la pagina
function cambiaTab(tipo) {
    const tabLogin = document.getElementById('tab-login');
    const tabReg = document.getElementById('tab-registrati');
    const btnL = document.getElementById('btnLogin');
    const btnR = document.getElementById('btnRegistrati');

    if (tipo === 'login') {
        tabLogin.style.display = 'block';
        tabReg.style.display = 'none';
        btnL.classList.add('active');
        btnR.classList.remove('active');
    } else {
        tabLogin.style.display = 'none';
        tabReg.style.display = 'block';
        btnR.classList.add('active');
        btnL.classList.remove('active');
    }
}
</script>

<?php include 'includes/footer.php'; ?>