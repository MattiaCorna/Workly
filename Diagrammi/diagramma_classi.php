<?php
// Esercizio: Diagramma delle Classi
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagramma delle Classi</title>
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
    .chart-container {
      width: 70%;
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<a href="gpo.php" class="home-btn" title="Indietro">⬅️</a>

<div class="container">
  <h1>📋 Diagramma delle Classi</h1>
  <p class="description">Diagramma UML che mostra la struttura e le relazioni tra le classi del progetto</p>
  
  <div class="diagram-container">
    <img src="Diagrammi/DiagrammaClassi.png" alt="Diagramma delle Classi">
  </div>
</div>

</body>
</html>