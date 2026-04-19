<?php

function saveLeaderboardEntry() {
    $leaderboard = readJsonFile(LEADERBOARD_FILE);
    
    // Calculate total rolls (sum of both players if 2-player)
    $totalRolls = count($_SESSION['roll_history_p1']) + count($_SESSION['roll_history_p2']);
    
    // Calculate game duration
    $duration = time() - ($_SESSION['game_start_time'] ?? time());
    $durationFormatted = formatDuration($duration);
    
    $leaderboard[] = [
        'username' => $_SESSION['username'],
        'difficulty' => $_SESSION['difficulty'],
        'player_count' => $_SESSION['player_count'],
        'rolls' => $totalRolls,
        'duration_seconds' => $duration,
        'duration_formatted' => $durationFormatted,
        'date' => date('Y-m-d H:i:s')
    ];

    writeJsonFile(LEADERBOARD_FILE, $leaderboard);
}

function getLeaderboard($limit = 10) {
    $leaderboard = readJsonFile(LEADERBOARD_FILE);

    // Sort by rolls (fewest rolls first)
    usort($leaderboard, function ($a, $b) {
        if ($a['rolls'] == $b['rolls']) {
            return $a['duration_seconds'] <=> $b['duration_seconds'];
        }
        return $a['rolls'] <=> $b['rolls'];
    });

    return array_slice($leaderboard, 0, $limit);
}

function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return sprintf("%d min %d sec", $minutes, $remainingSeconds);
}
?>