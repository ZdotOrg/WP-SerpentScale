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

<div class="card">
    <h2>Game Board</h2>

    <div class="info-grid">
        <div class="info-box"><strong>Player:</strong> <?php echo sanitize($_SESSION['username']); ?></div>
        <div class="info-box"><strong>Difficulty:</strong> <?php echo sanitize($_SESSION['difficulty']); ?></div>
        <div class="info-box"><strong>Current Position:</strong> <?php echo $_SESSION['position']; ?></div>
        <div class="info-box"><strong>Last Roll:</strong> <?php echo $_SESSION['last_roll'] ?? 'None yet'; ?></div>
    </div>

    <?php if (!empty($_SESSION['winner'])): ?>
        <p class="success">🎉 You won the game!</p>
        <a class="btn" href="leaderboard.php">View Leaderboard</a>
    <?php else: ?>
        <form method="POST" class="actions">
            <button type="submit" name="roll">🎲 Roll Dice</button>
            <button type="submit" name="reset">Reset Game</button>
        </form>

        <?php if (!empty($_SESSION['last_roll'])): ?>
            <div class="dice">
                🎲 You rolled: <?php echo $_SESSION['last_roll']; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Board (1 to 100)</h3>

    <div class="board">
        <?php
        $number = 100;

        for ($row = 0; $row < 10; $row++) {
            $cells = [];

            for ($col = 0; $col < 10; $col++) {
                $cells[] = $number--;
            }

            if ($row % 2 !== 0) {
                $cells = array_reverse($cells);
            }

            foreach ($cells as $cellNumber) {
                $class = 'cell';

                if ($_SESSION['position'] == $cellNumber) {
                    $class .= ' player';
                } elseif (isset($config['snakes'][$cellNumber])) {
                    $class .= ' snake';
                } elseif (isset($config['ladders'][$cellNumber])) {
                    $class .= ' ladder';
                }
                ?>
                <div id="cell-<?php echo $cellNumber; ?>" class="<?php echo $class; ?>">
                    <?php if (isset($config['snakes'][$cellNumber])): ?>
                        <div class="cell-icon snake-icon">🐍</div>
                    <?php elseif (isset($config['ladders'][$cellNumber])): ?>
                        <div class="cell-icon ladder-icon">🪜</div>
                    <?php endif; ?>

                    <?php if ($_SESSION['position'] == $cellNumber): ?>
                        <div class="token">🟢</div>
                    <?php endif; ?>

                    <div class="cell-number"><?php echo $cellNumber; ?></div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<div class="card">
    <h3>Event Log</h3>
    <?php if (!empty($_SESSION['events_log'])): ?>
        <ul>
            <?php foreach (array_reverse($_SESSION['events_log']) as $event): ?>
                <li><?php echo sanitize($event); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No moves yet.</p>
    <?php endif; ?>
</div>

</main>
</body>
</html>