<?php
// Set custom session directory to avoid permission issues
// This creates 'sessions' folder at the same level as 'includes', 'data', etc.
$sessionPath = __DIR__ . '/../sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Now start the session
session_start();

define('USERS_FILE', __DIR__ . '/../data/users.json');
define('LEADERBOARD_FILE', __DIR__ . '/../data/leaderboard.json');
?>