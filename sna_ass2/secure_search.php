<?php
// secure_search.php
// Secure example: PDO prepared statements + output escaping
// NOTE: adapt DB credentials for your local test database (do NOT publish credentials).

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- DB connection (use local DB only) ---
$dsn = 'mysql:host=127.0.0.1;dbname=demo_db;charset=utf8mb4';
$user = 'dbuser';
$pass = 'dbpassword';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo "DB connection error (local test only): " . htmlspecialchars($e->getMessage());
    exit;
}

// --- Handle form submission securely ---
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the input (no direct concatenation into SQL)
    $name = trim($_POST['name'] ?? '');

    // Prepared statement with named parameter
    $stmt = $pdo->prepare('SELECT id, name, description FROM products WHERE name = :name LIMIT 50');
    $stmt->execute(['name' => $name]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Secure Product Search (Demo)</title>
  <style>body{font-family:system-ui,Segoe UI,Roboto,Arial;padding:20px}</style>
</head>
<body>
  <h1>Secure Product Search (Local demo)</h1>
  <form method="post" action="secure_search.php">
    <label>Product name:
      <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
    </label>
    <button type="submit">Search</button>
  </form>

  <h2>Results</h2>
  <?php if (empty($results)): ?>
    <p>No results to show.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($results as $row): ?>
        <li>
          <strong><?=htmlspecialchars($row['name'])?></strong><br>
          <?=nl2br(htmlspecialchars($row['description']))?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <hr>
  <p><em>Note:</em> This page uses prepared statements (PDO) and output escaping (htmlspecialchars) to prevent SQL injection and XSS.</p>
</body>
</html>
