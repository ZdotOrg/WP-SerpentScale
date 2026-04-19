<?php

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/leaderboard_logic.php';

function getBoardConfig($difficulty = 'medium') {
    $easy = [
        'snakes' => [
            99 => ['end' => 80, 'color' => '#8b0000'],  // Dark red
            92 => ['end' => 72, 'color' => '#B22222'],  // Firebrick
            74 => ['end' => 53, 'color' => '#DC143C']   // Crimson
        ],
        'ladders' => [
            3 => ['end' => 22, 'color' => '#228B22'],   // Forest green
            8 => ['end' => 26, 'color' => '#32CD32'],   // Lime green
            20 => ['end' => 38, 'color' => '#006400'],  // Dark green
            28 => ['end' => 55, 'color' => '#7CFC00'],  // Lawn green
            50 => ['end' => 72, 'color' => '#00FF00'],  // Green
            71 => ['end' => 91, 'color' => '#ADFF2F']   // Green yellow
        ]
    ];

    $medium = [
        'snakes' => [
            98 => ['end' => 79, 'color' => '#da6e6e'],
            95 => ['end' => 75, 'color' => '#B22222'],
            83 => ['end' => 19, 'color' => '#DC143C'],
            73 => ['end' => 53, 'color' => '#FF4500'],  // Orange red
            69 => ['end' => 33, 'color' => '#FF6347'],  // Tomato
            55 => ['end' => 7, 'color' => '#FF0000']     // Red
        ],
        'ladders' => [
            2 => ['end' => 38, 'color' => '#228B22'],
            9 => ['end' => 31, 'color' => '#32CD32'],
            21 => ['end' => 42, 'color' => '#006400'],
            28 => ['end' => 84, 'color' => '#7CFC00'],
            51 => ['end' => 67, 'color' => '#00FF00'],
            71 => ['end' => 91, 'color' => '#ADFF2F']
        ]
    ];

    $hard = [
        'snakes' => [
            99 => ['end' => 78, 'color' => '#8B0000'],
            95 => ['end' => 56, 'color' => '#B22222'],
            88 => ['end' => 24, 'color' => '#DC143C'],
            76 => ['end' => 41, 'color' => '#FF4500'],
            67 => ['end' => 12, 'color' => '#FF6347'],
            54 => ['end' => 16, 'color' => '#FF0000'],
            48 => ['end' => 10, 'color' => '#8B008B'],  // Dark magenta
            36 => ['end' => 6, 'color' => '#9400D3'],   // Dark violet
            32 => ['end' => 8, 'color' => '#9932CC']    // Dark orchid
        ],
        'ladders' => [
            4 => ['end' => 14, 'color' => '#228B22'],
            12 => ['end' => 29, 'color' => '#32CD32'],
            26 => ['end' => 47, 'color' => '#006400'],
            39 => ['end' => 60, 'color' => '#7CFC00'],
            63 => ['end' => 81, 'color' => '#00FF00']
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
        15 => ['type' => 'bonus', 'move' => 5, 'msg' => 'Found a hidden shortcut through the garden!', 'extra_turn' => false],
        23 => ['type' => 'penalty', 'move' => -3, 'msg' => 'Stumbled on a loose stone and slipped backward!', 'extra_turn' => false],
        42 => ['type' => 'warp', 'move' => 58, 'msg' => 'Discovered a magical portal that teleports you forward!', 'extra_turn' => false],
        56 => ['type' => 'skip', 'move' => 0, 'msg' => 'A wise sage shares knowledge — you gain insight but stay put.', 'extra_turn' => false, 'skip_turn' => true],
        67 => ['type' => 'bonus', 'move' => 8, 'msg' => 'Caught a friendly wind that carries you ahead!', 'extra_turn' => false],
        78 => ['type' => 'penalty', 'move' => -5, 'msg' => 'A mischievous sprite plays tricks on you!', 'extra_turn' => false],
        85 => ['type' => 'warp', 'move' => 95, 'msg' => 'A divine blessing launches you toward victory!', 'extra_turn' => false],
        // NEW: Mystery boost tile
        33 => ['type' => 'mystery', 'move' => 0, 'msg' => 'A mysterious chest appears before you!', 'extra_turn' => false],
        // NEW: Extra turn tile
        44 => ['type' => 'extra_turn', 'move' => 0, 'msg' => 'The gods favor you! You get another turn!', 'extra_turn' => true]
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
    $_SESSION['turn_count'] = 0;
    $_SESSION['skip_turn_p1'] = false;
    $_SESSION['skip_turn_p2'] = false;
    $_SESSION['extra_turn_p1'] = false;
    $_SESSION['extra_turn_p2'] = false;
    $_SESSION['game_start_time'] = time(); // Track game duration
    
    // For backward compatibility
    $_SESSION['position'] = 0;
    $_SESSION['roll_history'] = [];
}

function rollDice() {
    return rand(1, 6);
}


#BONUS MYSTERY EVENTS
function applyMysteryBoost($playerName, $currentPosition) {
    $outcomes = [
        ['move' => 10, 'msg' => 'The chest contains a magic carpet! +10 cells forward!'],
        ['move' => -5, 'msg' => 'Oh no! The chest was a mimic! -5 cells backward!'],
        ['move' => 0, 'msg' => 'The chest is empty. Nothing happens.'],
        ['move' => 20, 'msg' => 'Legendary artifact! +20 cells forward!'],
        ['move' => -3, 'msg' => 'A curse from the chest! -3 cells backward!']
    ];
    
    $outcome = $outcomes[array_rand($outcomes)];
    $newPosition = min(100, max(0, $currentPosition + $outcome['move']));
    
    $narrator = "🎁 {$playerName}: {$outcome['msg']} (Cell {$currentPosition} → {$newPosition})";
    $_SESSION['events_log'][] = $narrator;
    $_SESSION['last_event'] = $narrator;
    
    return $newPosition;
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
            $newPosition = max(0, $currentPosition + $event['move']);
            $narrator = "⚠️ {$playerName}: {$event['msg']} (Cell {$currentPosition} → {$newPosition})";
            break;
            
        case 'warp':
            $newPosition = min(100, $event['move']);
            $narrator = "🌀 {$playerName}: {$event['msg']} (Cell {$currentPosition} → {$newPosition})";
            break;
            
        case 'skip':
            $newPosition = $currentPosition;
            $playerKey = "skip_turn_p{$_SESSION['current_player']}";
            $_SESSION[$playerKey] = true;
            $narrator = "📖 {$playerName}: {$event['msg']} - Next turn will be skipped!";
            break;
            
        case 'extra_turn':
            $newPosition = $currentPosition;
            $playerKey = "extra_turn_p{$_SESSION['current_player']}";
            $_SESSION[$playerKey] = true;
            $narrator = "🔄 {$playerName}: {$event['msg']} - You get an extra turn!";
            break;
            
        case 'mystery':
            $newPosition = applyMysteryBoost($playerName, $currentPosition);
            return $newPosition; // Already logged in mystery function
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
    
    // Check if player has skip turn flag
    $skipKey = "skip_turn_p{$currentPlayer}";
    if (isset($_SESSION[$skipKey]) && $_SESSION[$skipKey] === true) {
        $_SESSION[$skipKey] = false;
        $skipMessage = "⏭️ {$playerName} skips this turn due to previous event!";
        $_SESSION['events_log'][] = $skipMessage;
        $_SESSION['last_event'] = $skipMessage;
        
        // Switch turns
        if ($playerCount == 2) {
            $_SESSION['current_player'] = $currentPlayer == 1 ? 2 : 1;
        }
        return false;
    }
    
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
        $_SESSION['events_log'][] = $message;
    } else {
        // Check for ladders first (using new structure with 'end' key)
        if (isset($ladders[$newPosition]) && isset($ladders[$newPosition]['end'])) {
            $start = $newPosition;
            $newPosition = $ladders[$newPosition]['end'];  // Access the 'end' value
            $narratorMsg = getNarratorMessage('ladder', $start, $newPosition, $playerName);
            $_SESSION['events_log'][] = $narratorMsg;
            $message = $narratorMsg;
        } 
        // Then check for snakes (using new structure with 'end' key)
        elseif (isset($snakes[$newPosition]) && isset($snakes[$newPosition]['end'])) {
            $start = $newPosition;
            $newPosition = $snakes[$newPosition]['end'];  // Access the 'end' value
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
    
    // Check for win condition
    if ($newPosition === 100) {
        $_SESSION['winner'] = $playerName;
        saveLeaderboardEntry(); // Now calls the leaderboard function
        return true; // Game ended
    }
    
    // Handle extra turn
    $extraKey = "extra_turn_p{$currentPlayer}";
    $hasExtraTurn = isset($_SESSION[$extraKey]) && $_SESSION[$extraKey] === true;
    
    if ($hasExtraTurn) {
        $_SESSION[$extraKey] = false;
        $extraMessage = "🎲 {$playerName} gets an extra turn!";
        $_SESSION['events_log'][] = $extraMessage;
        $_SESSION['last_event'] = $extraMessage;
        // Don't switch players - same player goes again
    } 
    // Switch turns in 2-player mode (if no extra turn)
    elseif ($playerCount == 2) {
        $_SESSION['current_player'] = $currentPlayer == 1 ? 2 : 1;
    }
    
    return false; // Game continues
}

function renderBoard() {
    $positions = [
        1 => $_SESSION['position_p1'] ?? 0,
        2 => $_SESSION['position_p2'] ?? 0
    ];
    $playerCount = $_SESSION['player_count'] ?? 1;
    $config = getBoardConfig($_SESSION['difficulty'] ?? 'medium');
    $snakes = $config['snakes'];
    $ladders = $config['ladders'];
    $eventCells = getEventCells();
    
    $html = '<div class="game-board-wrapper">';
    $html .= '<div class="game-board">';
    
    for ($row = 0; $row < 10; $row++) {
        $html .= '<div class="board-row">';
        
        $isEvenRow = ($row % 2 == 0);
        $startCell = 100 - ($row * 10);
        
        for ($col = 0; $col < 10; $col++) {
            if ($isEvenRow) {
                $cellNum = $startCell - $col;
            } else {
                $cellNum = $startCell - 9 + $col;
            }
            
            // Determine special cell type
            $specialType = '';
            $specialEnd = '';
            $specialColor = '';
            
            if (isset($snakes[$cellNum])) {
                $specialType = 'snake';
                $specialEnd = $snakes[$cellNum]['end'];
                $specialColor = $snakes[$cellNum]['color'];
            } elseif (isset($ladders[$cellNum])) {
                $specialType = 'ladder';
                $specialEnd = $ladders[$cellNum]['end'];
                $specialColor = $ladders[$cellNum]['color'];
            } elseif (isset($eventCells[$cellNum])) {
                $specialType = $eventCells[$cellNum]['type'];
                $specialColor = getEventColor($eventCells[$cellNum]['type']);
            }
            
            // Build inline style for color
            $style = $specialColor ? 'style="background: linear-gradient(135deg, ' . $specialColor . ', ' . adjustBrightness($specialColor, -30) . ');"' : '';
            
            // Determine which players are on this cell
            $playersHere = [];
            if ($positions[1] == $cellNum) $playersHere[] = 1;
            if ($playerCount == 2 && $positions[2] == $cellNum) $playersHere[] = 2;
            
            // Build player markers HTML (Purple for P1, Forest Green for P2)
            $playerMarkers = '';
            if (!empty($playersHere)) {
                $playerMarkers = '<div class="player-marker">';
                foreach ($playersHere as $p) {
                    $playerClass = ($p == 1) ? 'player-p1' : 'player-p2';
                    $playerMarkers .= '<span class="' . $playerClass . '">P' . $p . '</span>';
                }
                $playerMarkers .= '</div>';
            }
            
            // Build special info (ONLY destination arrow, no small emoji)
            $specialInfo = '';
            if ($specialType) {
                $icon = match($specialType) {
                    'snake' => '🐍',
                    'ladder' => '🪜',
                    'bonus' => '✨',
                    'penalty' => '⚠️',
                    'warp' => '🌀',
                    'skip' => '📖',
                    'mystery' => '🎁',
                    'extra_turn' => '🔄',
                    default => '✨'
                };
                
                $specialInfo = '<div class="special-info">';
                $specialInfo .= '<span class="special-icon">' . $icon . '</span>';
                if ($specialEnd) {
                    $specialInfo .= '<span class="special-destination">→ ' . $specialEnd . '</span>';
                }
                $specialInfo .= '</div>';
            }
            
            $html .= sprintf(
                '<div class="board-cell %s" %s data-cell="%d">
                    <span class="cell-number">%d</span>
                    %s
                    %s
                </div>',
                $specialType ?: '',
                $style,
                $cellNum,
                $cellNum,
                $specialInfo,
                $playerMarkers
            );
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * Helper function to darken a color for gradient
 */
function adjustBrightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function getEventColor($type) {
    switch ($type) {
        case 'bonus':
            return '#4CAF50';
        case 'penalty':
            return '#F44336';
        case 'warp':
            return '#9C27B0';
        case 'skip':
            return '#FF9800';
        default:
            return '#9E9E9E';
    }
}
// NEW: Function to check if player has extra turn
function hasExtraTurn($playerId) {
    $key = "extra_turn_p{$playerId}";
    return isset($_SESSION[$key]) && $_SESSION[$key] === true;
}

// NEW: Function to reset game state
function resetGame() {
    if (isset($_SESSION['winner'])) {
        unset($_SESSION['winner']);
    }
    $_SESSION['position_p1'] = 0;
    $_SESSION['position_p2'] = 0;
    $_SESSION['roll_history_p1'] = [];
    $_SESSION['roll_history_p2'] = [];
    $_SESSION['current_player'] = 1;
    $_SESSION['turn_count'] = 0;
    $_SESSION['skip_turn_p1'] = false;
    $_SESSION['skip_turn_p2'] = false;
    $_SESSION['extra_turn_p1'] = false;
    $_SESSION['extra_turn_p2'] = false;
    $_SESSION['game_start_time'] = time();
}
?>