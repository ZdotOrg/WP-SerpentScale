<?php
$currentUser = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serpent Scale</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <h1>🐍 Serpent Scale</h1>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if ($currentUser): ?>
                <a href="game.php">Game</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">