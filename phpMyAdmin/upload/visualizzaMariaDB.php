<?php
    
    $connessione= new mysqli("localhost","utente_phpmyadmin","password_sicura","LOGIN");
    if ($connessione->connect_errno){
        die($connessione->connect_error);
    }
    
    $interrogazione="SELECT * from utente;";
    $risultato=$connessione->query($interrogazione);
    if (!$risultato)
        die ($connessione->error);
    var_dump($risultato);

?>