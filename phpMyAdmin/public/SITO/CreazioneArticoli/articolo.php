<?php
// Classe Articolo di esempio
class Articolo {
    public $nome; // Nome dell'articolo
    public $descrizione; // Descrizione dell'articolo
    public $prezzo; // Prezzo dell'articolo
    public $immagine; // Nome file immagine dell'articolo

    // Costruttore: inizializza le proprietà dell'articolo
    public function __construct($nome, $descrizione, $prezzo, $immagine) {
        $this->nome = $nome; // Assegna il nome
        $this->descrizione = $descrizione; // Assegna la descrizione
        $this->prezzo = $prezzo; // Assegna il prezzo
        $this->immagine = $immagine; // Assegna il nome file immagine
    }

    // Mostra l'articolo in una card (HTML)
    public function show() {
        // Crea un contenitore card con immagine e dettagli articolo
        echo '<div class="card" style="width: 18rem;">'; // Inizio card
        echo '<img src="IMG/' . htmlspecialchars($this->immagine) . '" class="card-img-top" alt="Immagine articolo">'; // Mostra l'immagine dalla cartella locale
        echo '<div class="card-body">'; // Corpo della card
        echo '<h5 class="card-title">' . htmlspecialchars($this->nome) . '</h5>'; // Titolo card
        echo '<p class="card-text">' . htmlspecialchars($this->descrizione) . '</p>'; // Descrizione
        echo '<p class="card-text"><strong>Prezzo: </strong>' . htmlspecialchars($this->prezzo) . ' €</p>'; // Prezzo
        echo '</div></div>'; // Fine card
    }
}
?>
