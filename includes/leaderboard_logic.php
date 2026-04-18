<?php

function saveLeaderboardEntry() {
    $leaderboard = readJsonFile(LEADERBOARD_FILE);

    $leaderboard[] = [
        'username' => $_SESSION['username'],
        'difficulty' => $_SESSION['difficulty'],
        'rolls' => count($_SESSION['roll_history']),
        'date' => date('Y-m-d H:i:s')
    ];

    writeJsonFile(LEADERBOARD_FILE, $leaderboard);
}

function getLeaderboard() {
    $leaderboard = readJsonFile(LEADERBOARD_FILE);

    usort($leaderboard, function ($a, $b) {
        return $a['rolls'] <=> $b['rolls'];
    });

    return $leaderboard;
}
?>
    </main>
</body>
</html>