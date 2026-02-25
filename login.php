<?php
session_start();
require_once 'includes/db_connect.php'; // Collega il ponte con PostgreSQL

$errore_login = "";
$errore_reg = "";

// --- LOGICA REGISTRAZIONE ---
if (isset($_POST['submit_registrazione'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 
    $giocatore = $_POST['giocatore'];

    try {
        $sql = "INSERT INTO utenti (nome, email, password, giocatore_preferito) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $password, $giocatore]);
        
        // Se va a buon fine, lo logghiamo subito
        $_SESSION['id_utente'] = $pdo->lastInsertId();
        $_SESSION['nome'] = $nome;
        $_SESSION['ruolo'] = 'tifoso';
        header("Location: curva.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) { 
            $errore_reg = "Email già registrata, bro!";
        } else {
            $errore_reg = "Errore durante la registrazione.";
        }
    }
}

// --- LOGICA LOGIN ---
if (isset($_POST['submit_login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM utenti WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $utente = $stmt->fetch();

    if ($utente && password_verify($password, $utente['password'])) {
        // Password corretta! Salviamo i dati in sessione
        $_SESSION['id_utente'] = $utente['id'];
        $_SESSION['nome'] = $utente['nome'];
        $_SESSION['ruolo'] = $utente['ruolo'];
        header("Location: curva.php");
        exit;
    } else {
        $errore_login = "Email o password errati.";
    }
}

// --- LOGICA LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 1. Richiamiamo la TESTA del sito (HTML, CSS, Menu)
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
                <?php if(isset($errore_login)) { echo "<p class='errore'>$errore_login</p>"; } ?>
                
                <form id="formLogin" action="login.php" method="POST" onsubmit="return validaLogin()">
                    <div class="form-group">
                        <label for="login-email">Email:</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password:</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    
                    <p id="errore-js-login" class="errore-js"></p>
                    <button type="submit" name="submit_login" class="btn-primary">Entra</button>
                </form>
            </div>

            <div id="tab-registrati" class="tab-content" style="display:none;">
                <?php if(isset($errore_reg)) { echo "<p class='errore'>$errore_reg</p>"; } ?>
                
                <form id="formRegister" action="login.php" method="POST" onsubmit="return validaRegistrazione()">
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
                    
                    <p id="errore-js-reg" class="errore-js"></p>
                    <button type="submit" name="submit_registrazione" class="btn-primary">Registrati</button>
                </form>
            </div>
        </section>
    </main>
    <script src="js/script.js"></script>

    <?php 
    // 2. Richiamiamo la CODA del sito (Motto, Copyright)
    include 'includes/footer.php';
    ?>
    
    
