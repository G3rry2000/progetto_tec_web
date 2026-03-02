<?php
// Parametri di connessione (uguali per te e i tuoi colleghi)
$host = "localhost";
$port = "5432"; // La porta standard di PostgreSQL
$dbname = "morra_db"; // INSERISCI QUI IL NOME DEL TUO DATABASE!
$user = "www";
$password = "www";

// Creazione della stringa di connessione
$stringa_connessione = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Tentativo di connessione
$db = pg_connect($stringa_connessione);

// Controllo se la connessione è fallita
if (!$db) {
    die("Errore fatale: Impossibile connettersi al database PostgreSQL.");
} 
// (Se non esce nessun messaggio, significa che la connessione è perfetta!)

// ==========================================
// CONNESSIONE 2: PER IL LOGIN/CURVA (PDO)
// ==========================================
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$opzioni = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $opzioni);
} catch (\PDOException $e) {
    die("Errore di connessione PDO: " . $e->getMessage());
}
?>