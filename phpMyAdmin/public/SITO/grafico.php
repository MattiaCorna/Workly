<?php
// Dati simulati (potrebbero venire da un database)
$etichette = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio"];
$valori = [10, 25, 15, 30, 20];
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grafico Orizzontale in PHP</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<div class="chart-container">
  <h2>Vendite Mensili (Barra Orizzontale)</h2>
  <canvas id="graficoBarra"></canvas>
</div>

<script>
  const ctx = document.getElementById('graficoBarra');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($etichette); ?>,
      datasets: [{
        label: 'Vendite',
        data: <?php echo json_encode($valori); ?>,
        borderWidth: 1,
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
      }]
    },
    options: {
      indexAxis: 'y', // rende il grafico orizzontale
      scales: {
        x: {
          beginAtZero: true
        }
      }
    }
  });
</script>

</body>
</html>