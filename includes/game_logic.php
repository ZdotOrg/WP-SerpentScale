<?php

function getBoardConfig($difficulty = 'medium') {
    $easy = [
        'snakes' => [
            99 => 80,
            92 => 72,
            74 => 53
        ],
        'ladders' => [
            3 => 22,
            8 => 26,
            20 => 38,
            28 => 55,
            50 => 72,
            71 => 91
        ]
    ];

    $medium = [
        'snakes' => [
            98 => 79,
            95 => 75,
            83 => 19,
            73 => 53,
            69 => 33,
            55 => 7
        ],
        'ladders' => [
            2 => 38,
            9 => 31,
            21 => 42,
            28 => 84,
            51 => 67,
            71 => 91
        ]
    ];

    $hard = [
        'snakes' => [
            99 => 78,
            95 => 56,
            88 => 24,
            76 => 41,
            67 => 12,
            54 => 16,
            48 => 10,
            36 => 6,
            32 => 8
        ],
        'ladders' => [
            4 => 14,
            12 => 29,
            26 => 47,
            39 => 60,
            63 => 81
        ]
    ];

    if ($difficulty === 'easy') {
        return $easy;
    }

    if ($difficulty === 'hard') {
        return $hard;
    }

    return $medium;
}

function getEventCells() {
    return [
        15 => ['type' => 'bonus', 'move' => 5, 'msg' => 'Found a hidden shortcut through the garden!'],
        23 => ['type' => 'penalty', 'move' => -3, 'msg' => 'Stumbled on a loose stone and slipped backward!'],
        42 => ['type' => 'warp', 'move' => 58, 'msg' => 'Discovered a magical portal that teleports you forward!'],
        56 => ['type' => 'skip', 'move' => 0, 'msg' => 'A wise sage shares knowledge — you gain insight but stay put.'],
        67 => ['type' => 'bonus', 'move' => 8, 'msg' => 'Caught a friendly wind that carries you ahead!'],
        78 => ['type' => 'penalty', 'move' => -5, 'msg' => 'A mischievous sprite plays tricks on you!'],
        85 => ['type' => 'warp', 'move' => 95, 'msg' => 'A divine blessing launches you toward victory!']
    ];
}

function initializeGame($difficulty = 'medium', $playerCount = 1) {
    $_SESSION['difficulty'] = $difficulty;
    $_SESSION['player_count'] = $playerCount;
    $_SESSION['current_player'] = 1;
    $_SESSION['position_p1'] = 0;
    $_SESSION['position_p2'] = 0;
    $_SESSION['roll_history_p1'] = [];
    $_SESSION['roll_history_p2'] = [];
    $_SESSION['events_log'] = [];
    $_SESSION['winner'] = null;
    $_SESSION['last_roll'] = null;
    $_SESSION['last_event'] = null;
    
    // For backward compatibility
    $_SESSION['position'] = 0;
    $_SESSION['roll_history'] = [];
}

function rollDice() {
    return rand(1, 6);
}

function applyEvent($event, $currentPosition, $playerName) {
    $newPosition = $currentPosition;
    $narrator = '';
    
    switch ($event['type']) {
        case 'bonus':
            $newPosition = min(100, $currentPosition + $event['move']);
            $narrator = "✨ {$playerName}: {$event['msg']} (Cell {$currentPosition} → {$newPosition})";
            break;
            
        case 'penalty':
            $newPosition = max(0, $currentPosition + $event['move']); // move is negative
            $narrator = "⚠️ {$playerName}: {$event['msg']} (Cell {$currentPosition} → {$newPosition})";
            break;
            
        case 'warp':
            $newPosition = min(100, $event['move']);
            $narrator = "🌀 {$playerName}: {$event['msg']} (Cell {$currentPosition} → {$newPosition})";
            break;
            
        case 'skip':
            $newPosition = $currentPosition;
            $narrator = "📖 {$playerName}: {$event['msg']}";
            break;
    }
    
    $_SESSION['last_event'] = $narrator;
    $_SESSION['events_log'][] = $narrator;
    
    return $newPosition;
}

function getNarratorMessage($type, $from, $to, $playerName) {
    $messages = [
        'snake' => [
            "🐍 The serpent hisses — {$playerName} is dragged from cell {$from} down to cell {$to}!",
            "🐍 A venomous viper strikes! {$playerName} slides from {$from} to {$to}!",
            "🐍 The great serpent coils around {$playerName}, pulling them from {$from} to {$to}!"
        ],
        'ladder' => [
            "🪜 {$playerName} climbs the ancient ladder from cell {$from} to {$to}!",
            "🪜 Fortune smiles! {$playerName} ascends from {$from} to {$to}!",
            "🪜 The ladder of destiny carries {$playerName} from {$from} to {$to}!"
        ]
    ];
    
    $options = $messages[$type];
    $index = $_SESSION['turn_count'] % count($options);
    return $options[$index];
}

function processMove($roll) {
    $playerCount = $_SESSION['player_count'] ?? 1;
    $currentPlayer = $_SESSION['current_player'] ?? 1;
    $playerName = $currentPlayer == 1 ? ($_SESSION['username'] ?? 'Player 1') : 'Player 2';
    
    $posKey = "position_p{$currentPlayer}";
    $historyKey = "roll_history_p{$currentPlayer}";
    
    $config = getBoardConfig($_SESSION['difficulty']);
    $snakes = $config['snakes'];
    $ladders = $config['ladders'];
    $eventCells = getEventCells();
    
    $oldPosition = $_SESSION[$posKey];
    $newPosition = $oldPosition + $roll;
    $message = "{$playerName} rolled a {$roll} and moved from {$oldPosition} to {$newPosition}.";
    
    // Initialize turn count for narrator variety
    if (!isset($_SESSION['turn_count'])) {
        $_SESSION['turn_count'] = 0;
    }
    $_SESSION['turn_count']++;
    
    if ($newPosition > 100) {
        $newPosition = $oldPosition;
        $message = "{$playerName} rolled a {$roll}, but needed an exact roll to reach 100.";
    } else {
        // Check for ladders first
        if (isset($ladders[$newPosition])) {
            $start = $newPosition;
            $newPosition = $ladders[$newPosition];
            $narratorMsg = getNarratorMessage('ladder', $start, $newPosition, $playerName);
            $_SESSION['events_log'][] = $narratorMsg;
            $message = $narratorMsg;
        } 
        // Then check for snakes
        elseif (isset($snakes[$newPosition])) {
            $start = $newPosition;
            $newPosition = $snakes[$newPosition];
            $narratorMsg = getNarratorMessage('snake', $start, $newPosition, $playerName);
            $_SESSION['events_log'][] = $narratorMsg;
            $message = $narratorMsg;
        }
        // Then check for event cells
        elseif (isset($eventCells[$newPosition])) {
            $newPosition = applyEvent($eventCells[$newPosition], $newPosition, $playerName);
        } else {
            $_SESSION['events_log'][] = $message;
        }
    }
    
    $_SESSION[$posKey] = $newPosition;
    $_SESSION['last_roll'] = $roll;
    $_SESSION[$historyKey][] = $roll;
    
    // Update backward compatibility position
    $_SESSION['position'] = $_SESSION['position_p1'];
    $_SESSION['roll_history'] = $_SESSION['roll_history_p1'];
    
    if ($newPosition === 100) {
        $_SESSION['winner'] = $playerName;
        saveLeaderboardEntry($currentPlayer);
    } else {
        // Switch turns in 2-player mode
        if ($playerCount == 2) {
            $_SESSION['current_player'] = $currentPlayer == 1 ? 2 : 1;
        }
    }
}
?>