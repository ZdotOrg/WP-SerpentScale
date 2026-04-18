<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/leaderboard_logic.php';

$leaderboard = getLeaderboard();

include 'includes/header.php';
?>

<div class="card">
    <h2>Leaderboard</h2>

    <?php if (empty($leaderboard)): ?>
        <p>No games completed yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Difficulty</th>
                    <th>Rolls</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $entry): ?>
                    <tr>
                        <td><?php echo sanitize($entry['username']); ?></td>
                        <td><?php echo sanitize($entry['difficulty']); ?></td>
                        <td><?php echo $entry['rolls']; ?></td>
                        <td><?php echo sanitize($entry['date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</main>
</body>
</html>