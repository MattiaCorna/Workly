<?php
session_start(); // Avvia la sessione PHP
if (!isset($_SESSION['loggato']) || $_SESSION['loggato'] !== true) { // Controlla se l'utente è loggato
    header('Location: login.php'); // Se non è loggato, reindirizza alla pagina di login
    exit; // Termina l'esecuzione dello script
}
?>
<!doctype html> <!-- Dichiara il tipo di documento come HTML5 -->
<html lang="it"> <!-- Inizio del documento HTML, lingua italiana -->
    <head> <!-- Inizio dell'header della pagina -->
        <title>Corna Mattia Progetti scolastici 25/26</title> <!-- Titolo della pagina -->
        <!-- Required meta tags --> <!-- Meta tag necessari per la corretta visualizzazione -->
        <meta charset="utf-8" /> <!-- Set di caratteri UTF-8 -->
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        /> <!-- Rende la pagina responsive sui dispositivi mobili -->

    <!-- Bootstrap CSS v5.2.1: importa lo stile grafico --> <!-- Inclusione del CSS di Bootstrap -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        /> <!-- Collegamento al file CSS di Bootstrap tramite CDN -->
    </head> <!-- Fine dell'header -->


    <body> <!-- Inizio del corpo della pagina -->
        <header>
            <!-- place navbar here --> <!-- Qui può essere inserita la barra di navigazione -->
        </header>
                <main>
                    <div class="container mt-5"> <!-- Contenitore principale con margine superiore -->
                        <h2 class="text-center mb-4">Scegli una materia</h2> <!-- Titolo centrato -->
                        <div class="d-flex justify-content-center gap-3"> <!-- Pulsanti centrati con spazio -->
                            <!-- Pulsanti per scegliere la materia: portano alle rispettive pagine -->
                            <a href="informatica.php" class="btn btn-success btn-lg">INFORMATICA</a> <!-- Pulsante Informatica -->
                            <a href="tep.php" class="btn btn-primary btn-lg">TPS</a> <!-- Pulsante TPS -->
                            <a href="gpo.php" class="btn btn-warning btn-lg">GPO</a> <!-- Pulsante GPO -->
                        </div>
                    </div>
                </main>
        <footer>
            <!-- place footer here --> <!-- Qui può essere inserito il piè di pagina -->
        </footer>
    <!-- Bootstrap JavaScript Libraries: necessari per alcune funzionalità grafiche --> <!-- Inclusione delle librerie JavaScript di Bootstrap -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script> <!-- Inclusione di Popper.js necessario per alcuni componenti Bootstrap -->

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script> <!-- Inclusione del file JavaScript di Bootstrap tramite CDN -->
    </body> <!-- Fine del corpo della pagina -->
</html> <!-- Fine del documento HTML -->
