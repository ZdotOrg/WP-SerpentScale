<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/game_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['difficulty'])) {
    initializeGame($_POST['difficulty']);
    header("Location: game.php");
    exit();
}

include 'includes/header.php';
?>

<div class="card hero-card">
    <h2>Welcome to Serpent Scale</h2>
    <p>Play a sleek PHP-powered Snakes and Ladders game with sessions, difficulty modes, and leaderboard tracking.</p>

    <?php if (isset($_SESSION['username'])): ?>
        <p>You are logged in as <strong><?php echo sanitize($_SESSION['username']); ?></strong></p>

        <form method="POST" style="max-width: 400px; margin: 20px auto 0;">
            <label for="difficulty">Choose Difficulty</label>
            <select name="difficulty" id="difficulty">
                <option value="easy">Easy</option>
                <option value="medium" selected>Medium</option>
                <option value="hard">Hard</option>
            </select>
            <button type="submit">Start Game</button>
        </form>
    <?php else: ?>
        <p>Please log in or register to begin playing.</p>
        <div class="button-group">
            <a class="btn" href="login.php">Login</a>
            <a class="btn" href="register.php">Register</a>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>🎮 Game Features</h3>

    <div class="features-grid">
        <div class="feature">
            <h4>🎲 Dice Mechanics</h4>
            <p>Fully server-side dice rolling using PHP sessions.</p>
        </div>

        <div class="feature">
            <h4>🐍 Snakes & Ladders</h4>
            <p>Dynamic board with different difficulty levels.</p>
        </div>

        <div class="feature">
            <h4>🏆 Leaderboard</h4>
            <p>Track wins and performance without using SQL.</p>
        </div>

        <div class="feature">
            <h4>⚡ Real-Time State</h4>
            <p>Game state persists using PHP sessions.</p>
        </div>
    </div>
</div>

</main>
</body>
</html>