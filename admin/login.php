<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: students.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        header('Location: students.php');
        exit;
    }
    $error = 'Invalid username or password.';
}

$base = '../';
$title = 'Staff login';
include __DIR__ . '/../includes/header.php';
?>
<div class="mx-auto max-w-5xl grid lg:grid-cols-2 rounded-4xl overflow-hidden border border-line bg-white">

  <aside class="hidden lg:flex flex-col justify-between bg-ink text-white p-10">
    <div>
      <h1 class="font-display text-5xl font-semibold tracking-tight leading-[1.02]">Registry desk</h1>
      <p class="mt-4 text-white/75 max-w-xs">Review new registrations, approve students and keep the course list up to date.</p>
    </div>
    <ul class="mt-10 grid gap-3 text-sm text-white/80">
      <li class="flex items-center gap-3"><?= icon('search', 'w-5 h-5 text-sun') ?>Search and filter every student</li>
      <li class="flex items-center gap-3"><?= icon('check', 'w-5 h-5 text-sun') ?>Approve or reject in one click</li>
      <li class="flex items-center gap-3"><?= icon('book', 'w-5 h-5 text-sun') ?>Manage the course list</li>
    </ul>
  </aside>

  <section class="p-6 sm:p-10 lg:p-12" aria-labelledby="admin-title">
    <h2 id="admin-title" class="font-display text-3xl font-semibold tracking-tight">Staff login</h2>
    <p class="mt-2 text-ink/60">For registry staff only.</p>

    <?php if ($error): ?>
      <div role="alert" class="mt-6 flex items-center gap-3 rounded-2xl border border-bad/30 bg-bad/10 text-bad px-4 py-3 text-sm font-semibold">
        <?= icon('alert', 'w-5 h-5 shrink-0') ?><span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post" data-login-form novalidate class="mt-8 grid gap-5">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div>
        <label class="label" for="username">Username</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('user', 'w-5 h-5') ?></span>
          <input class="field field-icon" id="username" name="username" autocomplete="username" required>
        </div>
        <p class="error" data-error-for="username"></p>
      </div>
      <div>
        <label class="label" for="password">Password</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('lock', 'w-5 h-5') ?></span>
          <input class="field field-icon" type="password" data-password id="password" name="password" autocomplete="current-password" required>
        </div>
        <p class="error" data-error-for="password"></p>
      </div>
      <label class="flex items-center gap-2.5 text-sm font-medium cursor-pointer">
        <input type="checkbox" class="w-4 h-4 accent-[#3342E8]" data-toggle-password> Show password
      </label>
      <button type="submit" class="btn btn-primary w-full">Log in</button>
    </form>
  </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
