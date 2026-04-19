<?php

function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        file_put_contents($filePath, json_encode([]));
    }

    $data = file_get_contents($filePath);
    $decoded = json_decode($data, true);

    return is_array($decoded) ? $decoded : [];
}

function writeJsonFile($filePath, $data) {
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

function findUserByUsername($username) {
    $users = readJsonFile(USERS_FILE);

    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            return $user;
        }
    }

    return null;
}

function registerUser($username, $password) {
    $users = readJsonFile(USERS_FILE);

    $users[] = [
        'id' => uniqid(),
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    writeJsonFile(USERS_FILE, $users);
}

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
?>