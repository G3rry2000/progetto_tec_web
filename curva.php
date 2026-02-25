<?php
session_start();
require_once 'includes/db_connect.php'; // 1. Percorso aggiornato

// --- LOGICA SALVATAGGIO NUOVO MESSAGGIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['testo_messaggio']) && isset($_SESSION['id_utente'])) {
    $testo = trim($_POST['testo_messaggio']);
    if (!empty($testo)) {
        $sql = "INSERT INTO messaggi (id_utente, testo) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['id_utente'], $testo]);
        header("Location: curva.php");
        exit;
    }
}

// --- LOGICA ELIMINAZIONE MESSAGGIO (Solo Admin) ---
if (isset($_POST['elimina_messaggio']) && isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
    $id_da_eliminare = $_POST['id_messaggio'];
    $sql_del = "DELETE FROM messaggi WHERE id = ?";
    $stmt_del = $pdo->prepare($sql_del);
    $stmt_del->execute([$id_da_eliminare]);
    header("Location: curva.php");
    exit;
}

// --- RECUPERO MESSAGGI DAL DATABASE (SOLO SE LOGGATO) ---
$messaggi = [];
if (isset($_SESSION['id_utente'])) {
    $sql_messaggi = "SELECT m.id, m.testo, m.data_invio, u.nome, u.ruolo 
                     FROM messaggi m 
                     JOIN utenti u ON m.id_utente = u.id 
                     ORDER BY m.data_invio DESC";
    $stmt_msg = $pdo->query($sql_messaggi);
    $messaggi = $stmt_msg->fetchAll();
}

// 2. Dichiariamo il tuo CSS specifico e richiamiamo l'header dei compagni
$css_extra = "css/style2.css";
include 'includes/header.php';
?>

    <main class="container-curva">
        <section class="la-curva">
            <h2 class="titolo-chiaro">La Curva - Community</h2>
            
            <?php
            // SE L'UTENTE NON È LOGGATO
            if (!isset($_SESSION['id_utente'])) {
                echo "<div class='banner-ospite' style='margin-top: 40px;'>";
                echo "<h3 style='color: white; margin-bottom: 10px;'>🔒 Settore Esclusivo</h3>";
                echo "<p>Area Riservata. La bacheca della Curva è uno spazio esclusivo dedicato ai tifosi. Accedi o registrati al portale per leggere i messaggi e partecipare attivamente alla community.</p>";
                echo "<div style='margin-top: 20px;'>";
                echo "<a href='login.php' class='btn-primary' style='display: inline-block; width: auto; text-decoration: none; padding: 10px 25px;'>Unisciti alla Squadra</a>";
                echo "</div>";
                echo "</div>";
            } 
            // SE L'UTENTE È LOGGATO
            else {
                // Profilo Utente
                echo "<div class='profilo-riepilogo'>";
                echo "<h3>Bentornato in curva, " . htmlspecialchars($_SESSION['nome']) . "!</h3>";
                
                if (isset($_SESSION['ruolo'])) {
                    if ($_SESSION['ruolo'] === 'super_tifoso') {
                        echo "<span class='badge super-tifoso'>🔥 Super Attivo</span>";
                    } elseif ($_SESSION['ruolo'] === 'admin') {
                        echo "<span class='badge admin'>🛡️ Amministratore</span>";
                    } else {
                        echo "<span class='badge tifoso'>⚽ Tifoso</span>";
                    }
                }
                echo "</div>";
                
                // Form invio nuovo messaggio
                echo "<div class='nuovo-messaggio'>";
                echo "<form action='curva.php' method='POST'>";
                echo "<textarea name='testo_messaggio' placeholder='Carica la squadra...' required style='width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: 2px solid #ddd; box-sizing: border-box; resize: vertical;'></textarea>";
                echo "<button type='submit' class='btn-primary'>Grida in Curva</button>";
                echo "</form>";
                echo "</div>";
                
                // PULSANTE LOGOUT
                echo "<div style='margin-top: 20px; margin-bottom: 20px; text-align: center;'>";
                echo "<a href='login.php?logout=true' class='btn-logout'>Esci dallo stadio (Logout)</a>";
                echo "</div>";
                
                // Bacheca Messaggi
                echo "<div class='bacheca-messaggi' style='margin-top: 40px;'>";
                echo "<h3 class='titolo-chiaro'>Ultimi cori</h3>";
                
                if (count($messaggi) > 0) {
                    foreach ($messaggi as $msg) {
                        echo "<article class='messaggio'>";
                        echo "<header class='autore-info'>";
                        echo "<strong>" . htmlspecialchars($msg['nome']) . "</strong>";
                        
                        if ($msg['ruolo'] === 'super_tifoso') {
                            echo "<span class='badge super-tifoso'>🔥 Super Attivo</span>";
                        } elseif ($msg['ruolo'] === 'admin') {
                            echo "<span class='badge admin'>🛡️ Amministratore</span>";
                        } else {
                            echo "<span class='badge tifoso'>⚽ Tifoso</span>";
                        }
                        echo "</header>";
                        echo "<p class='testo-messaggio'>" . htmlspecialchars($msg['testo']) . "</p>";
                        
                        // Tasto Elimina per Admin
                        if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
                            echo "<form action='curva.php' method='POST' style='margin-top: 15px; text-align: right;'>";
                            echo "<input type='hidden' name='id_messaggio' value='" . $msg['id'] . "'>";
                            echo "<button type='submit' name='elimina_messaggio' style='background: #cc0000; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;'>🗑️ Rimuovi</button>";
                            echo "</form>";
                        }
                        echo "</article>";
                    }
                } else {
                    echo "<p style='color: white; text-align: center;'>Nessun coro in curva per ora. Inizia tu!</p>";
                }
                echo "</div>";
            }
            ?>

        </section>
    </main>
    <script src="js/script.js"></script>

    <?php 
    // 3. Percorso del footer aggiornato
    include 'includes/footer.php'; 
    ?>
    
    