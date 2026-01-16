<?php
session_start();
if (!isset($_SESSION['loggato']) || $_SESSION['loggato'] !== true) {
    header('Location: login.php');
    exit;
}
$articoli = [];
$file = 'articoli.json';
// Se è stato inviato un POST per eliminare un articolo
if (isset($_POST['elimina']) && isset($_POST['index'])) {
    // Legge il file JSON
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $articoli = json_decode($json, true);
        if (!is_array($articoli)) $articoli = [];
        // Rimuove l'articolo all'indice richiesto
        $index = (int)$_POST['index'];
        if (isset($articoli[$index])) {
            array_splice($articoli, $index, 1); // Elimina l'articolo dall'array
            // Salva il nuovo array nel file JSON
            file_put_contents($file, json_encode($articoli, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
// Ricarica la lista aggiornata
if (file_exists($file)) {
    $json = file_get_contents($file); // Legge il contenuto del file JSON
    $articoli = json_decode($json, true); // Converte il JSON in array PHP
    if (!is_array($articoli)) $articoli = [];
}
?>
<html>
<head>
    <title>Visualizza Articoli</title>
</head>
<body>
<h2>Tutti gli articoli</h2>
<?php
if (count($articoli) === 0) {
    echo "<p>Nessun articolo presente.</p>";
} else {
    foreach ($articoli as $i => $a) {
        $imgPath = 'IMG/' . $a['immagine']; // Percorso relativo alla cartella corrente
        // Mostra i dati dell'articolo in un box semplice
        echo "<div style='border:1px solid #aaa; margin:10px; padding:10px; width:300px;'>";
        // Mostra l'immagine dell'articolo (se esiste il file nella cartella IMG)
        echo "<img src='".htmlspecialchars($imgPath)."' alt='immagine' style='width:100%;max-width:250px;'><br>";
        echo "<b>Nome:</b> ".htmlspecialchars($a['nome'])."<br>";
        echo "<b>Descrizione:</b> ".htmlspecialchars($a['descrizione'])."<br>";
        echo "<b>Prezzo:</b> ".htmlspecialchars($a['prezzo'])." €<br>";
        // Form per eliminare l'articolo
        echo '<form method="post" style="margin-top:10px;">';
        echo '<input type="hidden" name="index" value="' . $i . '">'; // Indice dell'articolo
        echo '<button type="submit" name="elimina" style="color:white;background:red;border:none;padding:5px 10px;cursor:pointer;">Elimina</button>';
        echo '</form>';
        echo "</div>";
    }
}
?>
<br>
<a href="insert.php">Inserisci nuovo articolo</a> |
<a href="index.php">Torna alla Home</a>
</body>
</html>
