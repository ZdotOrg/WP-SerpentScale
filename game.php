<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/game_logic.php';
require_once 'includes/leaderboard_logic.php';

requireLogin();

if (!isset($_SESSION['position'])) {
    initializeGame('medium');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['roll']) && empty($_SESSION['winner'])) {
        $roll = rollDice();
        processMove($roll);
    }

    if (isset($_POST['reset'])) {
        initializeGame($_SESSION['difficulty'] ?? 'medium');
    }
}

$config = getBoardConfig($_SESSION['difficulty']);
include 'includes/header.php';
?>

<!-- Replace your existing game display with this -->
<div class="game-container">
    <div class="game-sidebar">
        <div class="card">
            <h2>Game Status</h2>
            <div class="info-grid">
                <div class="info-box"><strong>Player:</strong> <?php echo sanitize($_SESSION['username']); ?></div>
                <div class="info-box"><strong>Difficulty:</strong> <?php echo sanitize($_SESSION['difficulty']); ?></div>
                <div class="info-box"><strong>Position:</strong> <?php echo $_SESSION['position']; ?>/100</div>
                <div class="info-box"><strong>Last Roll:</strong> <?php echo $_SESSION['last_roll'] ?? 'None'; ?></div>
            </div>

            <?php if (!empty($_SESSION['winner'])): ?>
                <p class="success">🎉 You won the game!</p>
                <a class="btn" href="leaderboard.php">View Leaderboard</a>
            <?php else: ?>
                <form method="POST" class="actions">
                    <button type="submit" name="roll">🎲 Roll Dice</button>
                    <button type="submit" name="reset">Reset Game</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>Roll History</h3>
            <div class="roll-list">
                <?php foreach ($_SESSION['roll_history'] as $roll): ?>
                    <span class="roll-badge"><?php echo $roll; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="board-container">
        <div class="card">
            <h3>Game Board</h3>
            <div style="overflow-x: auto;"> <!-- Add this wrapper -->
                <?php echo renderBoard(); ?>
            </div>
        </div>
    </div


<div class="card">
    <h3>📜 Adventure Log</h3>
    <div class="events-log">
        <div class="log-entries">
            <?php 
            $events = array_slice($_SESSION['events_log'], -10);
            foreach (array_reverse($events) as $event): ?>
                <div class="log-entry"><?php echo sanitize($event); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</main>
</body>
</html>