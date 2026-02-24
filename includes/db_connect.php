<?php
// Parametri di connessione (uguali per te e i tuoi colleghi)
$host = "localhost";
$port = "5432"; // La porta standard di PostgreSQL
$dbname = "gruppoXX"; // INSERISCI QUI IL NOME DEL TUO DATABASE!
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
?>