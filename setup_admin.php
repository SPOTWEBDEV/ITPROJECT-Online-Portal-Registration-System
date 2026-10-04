<?php
// ONE-TIME SETUP: creates the first administrator account.
// It only works while the admins table is empty. DELETE THIS FILE after you use it.
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$message = '';
$done = false;

if ($count === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $message = 'Username must be 3 to 50 letters, numbers or underscores.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $done = true;
    }
}

$title = 'Create administrator';
include __DIR__ . '/includes/header.php';
?>
<div class="mx-auto max-w-md rounded-4xl bg-white border border-line p-8">
  <h1 class="font-display text-3xl font-semibold tracking-tight">Create administrator</h1>
  <?php if ($done): ?>
    <div role="status" class="mt-6 flex gap-3 rounded-2xl border border-ok/30 bg-ok/10 text-ok px-4 py-3 text-sm font-semibold">
      <?= icon('check', 'w-5 h-5 shrink-0') ?>
      <span>Administrator created. Delete setup_admin.php now, then log in from the Staff login link.</span>
    </div>
  <?php elseif ($count > 0): ?>
    <p class="mt-4 text-ink/70">An administrator already exists. Delete this file (setup_admin.php) from the server.</p>
  <?php else: ?>
    <p class="mt-2 text-ink/60">This page works once, while no administrator exists.</p>
    <?php if ($message): ?>
      <div role="alert" class="mt-5 flex gap-3 rounded-2xl border border-bad/30 bg-bad/10 text-bad px-4 py-3 text-sm font-semibold">
        <?= icon('alert', 'w-5 h-5 shrink-0') ?><span><?= e($message) ?></span>
      </div>
    <?php endif; ?>
    <form method="post" class="mt-6 grid gap-5">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div><label class="label" for="username">Username</label><input class="field" id="username" name="username" required></div>
      <div><label class="label" for="password">Password</label><input class="field" type="password" id="password" name="password" required></div>
      <button class="btn btn-primary w-full">Create administrator</button>
    </form>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
