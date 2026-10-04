<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, password FROM students WHERE email = ?');
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            session_regenerate_id(true);
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            header('Location: dashboard.php');
            exit;
        }
        // Same message for both cases so attackers cannot tell which part was wrong
        $error = 'Invalid email or password.';
    }
}

$title = 'Log in';
include __DIR__ . '/includes/header.php';
?>
<div class="mx-auto max-w-5xl grid lg:grid-cols-2 rounded-4xl overflow-hidden border border-line bg-white">

  <aside class="hidden lg:flex flex-col justify-between bg-brand text-white p-10">
    <div>
      <h1 class="font-display text-5xl font-semibold tracking-tight leading-[1.02]">Welcome back.</h1>
      <p class="mt-4 text-white/80 max-w-xs">Check your registration status, add courses and print your slip.</p>
    </div>
    <div class="relative mt-10" aria-hidden="true">
      <div class="rounded-3xl bg-white/12 border border-white/20 p-5">
        <div class="flex items-center gap-3">
          <span class="grid place-items-center w-10 h-10 rounded-full bg-sun text-ink"><?= icon('check', 'w-5 h-5') ?></span>
          <div>
            <p class="font-semibold leading-tight">Registration approved</p>
            <p class="text-sm text-white/70">Session <?= e(CURRENT_SESSION) ?></p>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <section class="p-6 sm:p-10 lg:p-12" aria-labelledby="login-title">
    <h2 id="login-title" class="font-display text-3xl font-semibold tracking-tight lg:hidden">Welcome back</h2>
    <h2 class="font-display text-3xl font-semibold tracking-tight hidden lg:block">Log in to your account</h2>
    <p class="mt-2 text-ink/60">Use the email address you registered with.</p>

    <?php if (isset($_GET['registered'])): ?>
      <div role="status" class="mt-6 flex items-center gap-3 rounded-2xl border border-ok/30 bg-ok/10 text-ok px-4 py-3 text-sm font-semibold">
        <?= icon('check', 'w-5 h-5 shrink-0') ?><span>Account created. You can log in now.</span>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div role="alert" class="mt-6 flex items-center gap-3 rounded-2xl border border-bad/30 bg-bad/10 text-bad px-4 py-3 text-sm font-semibold">
        <?= icon('alert', 'w-5 h-5 shrink-0') ?><span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post" data-login-form novalidate class="mt-8 grid gap-5">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div>
        <label class="label" for="email">Email address</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('mail', 'w-5 h-5') ?></span>
          <input class="field field-icon" type="email" id="email" name="email" autocomplete="email" placeholder="you@example.com" value="<?= e($email) ?>" required>
        </div>
        <p class="error" data-error-for="email"></p>
      </div>
      <div>
        <label class="label" for="password">Password</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('lock', 'w-5 h-5') ?></span>
          <input class="field field-icon" type="password" data-password id="password" name="password" autocomplete="current-password" placeholder="Your password" required>
        </div>
        <p class="error" data-error-for="password"></p>
      </div>
      <label class="flex items-center gap-2.5 text-sm font-medium cursor-pointer">
        <input type="checkbox" class="w-4 h-4 accent-[#3342E8]" data-toggle-password> Show password
      </label>
      <button type="submit" class="btn btn-primary w-full">Log in</button>
      <p class="text-sm text-ink/60 text-center">New here? <a class="font-semibold text-brand hover:underline" href="register.php">Create an account</a></p>
    </form>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
