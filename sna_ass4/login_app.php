<?php
// login_app.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// DB file
$dbfile = __DIR__ . '/users.sqlite';
$init = !file_exists($dbfile);

$pdo = new PDO('sqlite:' . $dbfile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($init) {
    // Create users table (simple schema)
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    // OPTIONAL: create a placeholder account instruction — use create_user.php instead
}

// Logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// If user is already logged in
if (isset($_SESSION['username'])) {
    $username = htmlspecialchars($_SESSION['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
} else {
    $username = null;
}

// Handle POST login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $input_user = trim($_POST['username']);
    $input_pass = $_POST['password'];

    if ($input_user === '' || $input_pass === '') {
        $login_error = "Please enter both username and password.";
    } else {
        // Prepared statement - prevents SQL injection
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $input_user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($input_pass, $row['password_hash'])) {
            // Regenerate session id on login
            session_regenerate_id(true);
            $_SESSION['username'] = $row['username'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Generic error message (do not reveal whether user exists)
            $login_error = "Invalid username or password.";
        }
    }
}

// small helper for html escaping
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Secure Login Demo (Sessions + Prepared Statements)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;max-width:720px;margin:20px auto;padding:0 16px}
    .box{border:1px solid #ddd;padding:16px;border-radius:8px}
    form{display:grid;gap:10px;max-width:420px}
    input[type=text], input[type=password]{width:100%;padding:8px}
    .error{color:#b00}
    .meta{color:#666;font-size:0.9em}
  </style>
</head>
<body>
  <h1>Secure Login Demo</h1>
  <p class="meta">Demonstrates safe session use and prevention of SQL injection using prepared statements. Runs locally only.</p>
<?php if ($username): ?>
  <div class="box">
    <p>Welcome, <strong><?=h($username)?></strong>!</p>
    <p class="meta">This page shows the username from the server-side session.</p>
    <p><a href="?action=logout">Logout</a></p>
  </div>
<?php else: ?>
  <div class="box">
    <h2>Login</h2>
    <?php if ($login_error): ?><p class="error"><?=h($login_error)?></p><?php endif; ?>
    <form method="post" action="<?=h($_SERVER['PHP_SELF'])?>">
      <label>
        Username
        <input type="text" name="username" autocomplete="username" required>
      </label>
      <label>
        Password
        <input type="password" name="password" autocomplete="current-password" required>
      </label>
      <button type="submit">Log in</button>
    </form>
    <hr>
    <p class="meta">To create a test user locally, run the included <code>create_user.php</code> helper (see README).</p>
  </div>
<?php endif; ?>
  <hr>
  <p class="meta">Security notes: user input is used only as a parameter in prepared statements (no concatenation). Passwords are stored as a hash (password_hash()). Do not run this app on a public server with real credentials.</p>
</body>
</html>
