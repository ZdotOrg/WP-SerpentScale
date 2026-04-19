<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/game_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['difficulty'])) {
    $playerCount = isset($_POST['player_count']) ? (int)$_POST['player_count'] : 1;
    initializeGame($_POST['difficulty'], $playerCount);
    header("Location: game.php");
    exit();
}

include 'includes/header.php';
?>

<div class="card hero-card">
    <h2>Welcome to Serpent Scale</h2>
    <p>Play a sleek PHP-powered Snakes and Ladders game with sessions, difficulty modes, AI events, and leaderboard tracking.</p>

    <?php if (isset($_SESSION['username'])): ?>
        <p>You are logged in as <strong><?php echo sanitize($_SESSION['username']); ?></strong></p>

        <form method="POST" style="max-width: 400px; margin: 20px auto 0;">
            <label for="difficulty">Choose Difficulty</label>
            <select name="difficulty" id="difficulty">
                <option value="easy">Easy (3 Snakes, 6 Ladders)</option>
                <option value="medium" selected>Medium (6 Snakes, 6 Ladders)</option>
                <option value="hard">Hard (9 Snakes, 5 Ladders)</option>
            </select>
            
            <label for="player_count">Number of Players</label>
            <select name="player_count" id="player_count">
                <option value="1" selected>Single Player</option>
                <option value="2">Two Players</option>
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
        
        <div class="feature">
            <h4>✨ AI Events</h4>
            <p>Land on special cells for bonuses, penalties, and warps!</p>
        </div>

        <div class="feature">
            <h4>🎭 AI Narrator</h4>
            <p>Dynamic storytelling brings every move to life.</p>
        </div>

        <div class="feature">
            <h4>👥 Two Players</h4>
            <p>Challenge a friend with turn-based gameplay.</p>
        </div>

        <div class="feature">
            <h4>📜 Adventure Recap</h4>
            <p>View complete event history at game end.</p>
        </div>
    </div>
</div>

</main>
</body>
</html>