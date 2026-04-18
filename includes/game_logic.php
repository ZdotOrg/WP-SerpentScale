<?php

function getBoardConfig($difficulty = 'medium') {
    $easy = [
        'snakes' => [
            99 => 80,
            92 => 72,
            74 => 53,
            64 => 43,
            49 => 30
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
            54 => 16
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

function initializeGame($difficulty = 'medium') {
    $_SESSION['difficulty'] = $difficulty;
    $_SESSION['position'] = 0;
    $_SESSION['roll_history'] = [];
    $_SESSION['events_log'] = [];
    $_SESSION['winner'] = null;
    $_SESSION['last_roll'] = null;
}

function rollDice() {
    return rand(1, 6);
}

function processMove($roll) {
    $config = getBoardConfig($_SESSION['difficulty']);
    $snakes = $config['snakes'];
    $ladders = $config['ladders'];

    $oldPosition = $_SESSION['position'];
    $newPosition = $oldPosition + $roll;
    $message = "You rolled a $roll and moved from $oldPosition to $newPosition.";

    if ($newPosition > 100) {
        $newPosition = $oldPosition;
        $message = "You rolled a $roll, but needed an exact roll to reach 100.";
    } else {
        if (isset($ladders[$newPosition])) {
            $start = $newPosition;
            $newPosition = $ladders[$newPosition];
            $message .= " Ladder! You climbed from $start to $newPosition.";
        } elseif (isset($snakes[$newPosition])) {
            $start = $newPosition;
            $newPosition = $snakes[$newPosition];
            $message .= " Snake! You slid from $start to $newPosition.";
        }
    }

    $_SESSION['position'] = $newPosition;
    $_SESSION['last_roll'] = $roll;
    $_SESSION['roll_history'][] = $roll;
    $_SESSION['events_log'][] = $message;

    if ($newPosition === 100) {
        $_SESSION['winner'] = $_SESSION['username'];
        saveLeaderboardEntry();
    }
}
?>
    </main>
</body>
</html>