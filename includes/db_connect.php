<?php
// PARAMETRI DI CONNESSIONE
$host     = "localhost";
$port     = "5432";
$dbname   = "morra_db";
$user     = "www";        
$password = "www";       

// Creazione della stringa di connessione per PostgreSQL
$stringa_connessione = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Tentativo di connessione 
$db = pg_connect($stringa_connessione);

// Controllo se la connessione è fallita
if (!$db) {
    die("Errore fatale: Impossibile connettersi al database PostgreSQL.");
}

?>