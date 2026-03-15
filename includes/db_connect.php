<?php
// =================================================================
// PARAMETRI DI CONNESSIONE (Username e Password richiesti dal PDF)
// =================================================================
$host     = "localhost";
$port     = "5432";
$dbname   = "morra_db"; // Nome che hai chiesto (Ma ricorda 'gruppoXX' per il PDF!)
$user     = "www";        // Username richiesto [cite: 22]
$password = "www";        // Password richiesta [cite: 24]

// Creazione della stringa di connessione per PostgreSQL
$stringa_connessione = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Tentativo di connessione (Senza PDO, come richiesto)
$db = pg_connect($stringa_connessione);

// Controllo se la connessione è fallita
if (!$db) {
    // Messaggio di errore utile per il debug durante il progetto
    die("Errore fatale: Impossibile connettersi al database PostgreSQL.");
}

// Se arrivi qui, la connessione è riuscita! 
// Non stampiamo nulla per non sporcare l'HTML del sito.
?>