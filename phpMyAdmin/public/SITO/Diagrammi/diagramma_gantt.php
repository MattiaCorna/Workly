<?php
// Esercizio: Diagramma di Gantt - Sequenziale

$attività = [
  "Analisi requisiti",
  "Progettazione UI/UX",
  "Sviluppo Frontend",
  "Sviluppo Backend",
  "Integrazione API",
  "Test e QA",
  "Distribuire"
];

$durate = [3, 4, 7, 8, 3, 5, 1];

$gantt = [];
$currentStart = 1;

for ($i = 0; $i < count($attività); $i++) {
  $gantt[] = [
    "task" => $attività[$i],
    "start" => $currentStart,
    "duration" => $durate[$i]
  ];
  // SEQUENZIALE: la prossima inizia ESATTAMENTE dove finisce questa
  $currentStart += $durate[$i];
}

// UI
$dayWidth = 26; // larghezza "unità"
$rowHeight = 40;
$totalDays = array_sum($durate);
$timelineWidth = $totalDays * $dayWidth;
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagramma di Gantt - Sequenziale</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    body {
      padding: 20px;
      background: #fafafa;
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
      z-index: 100;
    }

    .home-btn:hover {
      background: #764ba2;
      transform: scale(1.1);
    }

    h1 {
      margin: 0 0 20px;
      color: #2c3e50;
      text-align: center;
    }

    .wrap {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
      max-width: 1200px;
      margin: 0 auto;
      overflow-x: auto;
    }

    .row {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      gap: 15px;
    }

    .row:last-child {
      margin-bottom: 0;
    }

    .label {
      width: 200px;
      padding: 8px 12px;
      color: #222;
      font-weight: 600;
      font-size: 13px;
      background: #f0f7ff;
      border-radius: 6px;
      flex-shrink: 0;
    }

    .bar-area {
      position: relative;
      width: <?= $timelineWidth ?>px;
      height: <?= $rowHeight ?>px;
      background: 
        repeating-linear-gradient(
          to right,
          #f3f3f3 0,
          #f3f3f3 1px,
          transparent 1px,
          transparent <?= $dayWidth ?>px
        );
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #f0f0f0;
      flex-shrink: 0;
    }

    .bar {
      position: absolute;
      top: 3px;
      bottom: 3px;
      border-radius: 8px;
      background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
      box-shadow: 0 6px 16px rgba(52,152,219,0.25);
      display: flex;
      align-items: center;
      padding: 0 10px;
      color: white;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
      transition: all 0.3s ease;
    }

    .bar:hover {
      box-shadow: 0 8px 24px rgba(52,152,219,0.35);
      transform: translateY(-2px);
    }

    .stats {
      margin-top: 25px;
      padding: 15px;
      background: #e8f5e9;
      border-left: 4px solid #4CAF50;
      border-radius: 6px;
    }

    .stats p {
      margin: 5px 0;
      font-size: 14px;
      color: #2c5f2d;
    }

    .hint {
      color: #666;
      font-size: 12px;
      margin-top: 15px;
      text-align: center;
      padding-top: 15px;
      border-top: 1px solid #eee;
    }

    .legend {
      margin-top: 20px;
      padding: 12px;
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      border-radius: 6px;
      font-size: 12px;
      color: #856404;
    }
  </style>
</head>
<body>

<a href="../gpo.php" class="home-btn" title="Indietro">⬅️</a>

<h1>📅 Diagramma di Gantt - Progetto Sequenziale</h1>

<div class="wrap">
  <?php foreach ($gantt as $index => $item):
    $leftPx = ($item["start"] - 1) * $dayWidth;
    $widthPx = $item["duration"] * $dayWidth;
    $colors = [
      'rgba(52, 152, 219, 0.9)',
      'rgba(46, 204, 113, 0.9)',
      'rgba(155, 89, 182, 0.9)',
      'rgba(230, 126, 34, 0.9)',
      'rgba(231, 76, 60, 0.9)',
      'rgba(52, 73, 94, 0.9)',
      'rgba(26, 188, 156, 0.9)'
    ];
    $color = $colors[$index % count($colors)];
  ?>
  <div class="row">
    <div class="label">
      <span style="display: inline-block; width: 24px; height: 24px; background: <?= $color ?>; border-radius: 4px; margin-right: 8px; vertical-align: middle;"></span>
      <?= htmlspecialchars($item["task"]) ?>
    </div>
    <div class="bar-area">
      <div class="bar" style="left: <?= $leftPx ?>px; width: <?= $widthPx ?>px; background: <?= $color ?>;">
        <?= $item["duration"] ?> gg
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="stats">
  <p><strong>📊 Statistiche Progetto:</strong></p>
  <p>✓ Numero Attività: <?= count($attività) ?></p>
  <p>✓ Durata Totale: <?= $totalDays ?> giorni</p>
  <p>✓ Data Inizio: 01 Gen 2024</p>
  <p>✓ Data Fine: <?= date('d M Y', strtotime('+' . ($totalDays - 1) . ' days', strtotime('2024-01-01'))) ?></p>
</div>

<div class="legend">
  <strong>ℹ️ Legenda:</strong> Le attività sono disposte in sequenza. Ogni attività inizia quando la precedente finisce. La larghezza delle barre rappresenta la durata in giorni.
</div>

</body>
</html>
