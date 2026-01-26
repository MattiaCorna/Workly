<?php
// Pagina di lista esercizi GPO
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Esercizi GPO</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      background: #f4f6f8;
    }
    .home-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: #667eea;
      color: white;
      border: none;
      padding: 12px 16px;
      border-radius: 50%;
      font-size: 24px;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.3s, background 0.3s;
    }
    .home-btn:hover {
      background: #764ba2;
      transform: scale(1.1);
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1 {
      color: #2c3e50;
      text-align: center;
      margin-bottom: 30px;
    }
    .esercizi-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .esercizio-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 8px;
      text-decoration: none;
      transition: transform 0.3s, box-shadow 0.3s;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .esercizio-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 12px rgba(0,0,0,0.2);
    }
    .esercizio-card h3 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 18px;
    }
    .esercizio-card p {
      margin: 0;
      font-size: 14px;
      opacity: 0.9;
    }
  </style>
</head>
<body>

<a href="../SITO" class="home-btn" title="Home">🏠</a>

<div class="container">
  <h1>📊 Esercizi - GPO</h1>
  
  <div class="esercizi-list">
    <a href="Diagrammi/diagramma_classi.php" class="esercizio-card">
      <h3>Diagramma delle Classi</h3>
      <p>Visualizzazione del diagramma UML delle classi</p>
    </a>
    <a href="Diagrammi/diagramma_casi_uso.php" class="esercizio-card">
      <h3>Diagramma dei Casi d'Uso</h3>
      <p>Visualizzazione del diagramma UML dei casi d'uso</p>
    </a>
    <a href="Diagrammi/diagramma_er.php" class="esercizio-card">
      <h3>Diagramma ER</h3>
      <p>Visualizzazione del diagramma Entity-Relationship</p>
    </a>
    <a href="Diagrammi/diagramma_gantt.php" class="esercizio-card">
      <h3>Diagramma di Gantt</h3>
      <p>Pianificazione temporale delle attività del progetto</p>
    </a>
  </div>
</div>

</body>
</html>
