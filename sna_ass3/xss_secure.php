<?php
// xss_secure.php
// Safe comment demo: stores comments in SQLite and escapes output for HTML.


ini_set('display_errors', 1);
error_reporting(E_ALL);

// ----- SQLite DB (local file) -----
$dbfile = __DIR__ . '/comments.sqlite';
$init = !file_exists($dbfile);

$pdo = new PDO('sqlite:' . $dbfile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($init) {
    $pdo->exec("CREATE TABLE comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// ----- Handle POST (server-side validation only) -----
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if ($comment === '') {
        $errors[] = "Comment is required.";
    }

    if (empty($errors)) {
        // Use prepared statement (prevents SQL injection)
        $stmt = $pdo->prepare('INSERT INTO comments (name, comment) VALUES (:name, :comment)');
        $stmt->execute([':name' => $name, ':comment' => $comment]);
        // Redirect PRG pattern to avoid double-post on refresh
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch latest comments
$stmt = $pdo->query('SELECT id, name, comment, created_at FROM comments ORDER BY id DESC LIMIT 100');
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to safely render text into HTML
function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Secure Comment Demo (XSS-safe)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; max-width:800px; margin:20px auto; padding:0 16px; }
    form { margin-bottom: 20px; }
    textarea { width:100%; height:100px; }
    .comment { border:1px solid #ddd; padding:8px; margin-bottom:8px; border-radius:6px; }
    .meta { color:#666; font-size:0.9em; margin-bottom:6px; }
    .errors { color:#b00; margin-bottom:10px; }
  </style>
</head>
<body>
  <h1>Secure Comment Demo</h1>
  <p>This page demonstrates safe handling of user comments: server-side validation, prepared statements, and output escaping to prevent XSS. This demo runs locally only.</p>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <strong>Errors:</strong>
      <ul><?php foreach ($errors as $e): ?><li><?=h($e)?></li><?php endforeach;?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?=h($_SERVER['PHP_SELF'])?>">
    <label>
      Name:<br>
      <input type="text" name="name" value="<?=h($_POST['name'] ?? '')?>" required>
    </label><br><br>
    <label>
      Comment:<br>
      <textarea name="comment" required><?=h($_POST['comment'] ?? '')?></textarea>
    </label><br><br>
    <button type="submit">Post comment</button>
  </form>

  <h2>Recent comments</h2>
  <?php if (empty($comments)): ?>
    <p>No comments yet.</p>
  <?php else: ?>
    <?php foreach ($comments as $c): ?>
      <div class="comment">
        <div class="meta"><?=h($c['name'])?> • <?=h($c['created_at'])?></div>
        <div class="body"><?=nl2br(h($c['comment']))?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <hr>
  <p><em>Notes:</em> Output is escaped with <code>htmlspecialchars()</code>. In production also configure Content-Security-Policy headers and appropriate input validation.</p>
</body>
</html>
