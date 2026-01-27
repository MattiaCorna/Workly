<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        p {
            text-align: center;
            font-size: 18px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        a:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<a href="../index.php" class="home-btn" title="Home">🏠</a>

<div class="container">
    <h1>Benvenuto nella Dashboard</h1>
    <p>Sei loggato come <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <a href="logout.php">Logout</a>
</div>

</body>
</html>