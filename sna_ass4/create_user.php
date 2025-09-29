<?php
// create_user.php

if (PHP_SAPI !== 'cli') {
    echo "This script is for CLI only.\n";
    exit(1);
}

if ($argc < 3) {
    echo "Usage: php create_user.php <username> <password>\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

$dbfile = __DIR__ . '/users.sqlite';
$pdo = new PDO('sqlite:' . $dbfile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// create table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :hash)');
try {
    $stmt->execute([':username' => $username, ':hash' => $hash]);
    echo "User created: $username\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
