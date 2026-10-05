<?php
require __DIR__ . '/includes/auth.php';
$title = 'Register online';
include __DIR__ . '/includes/header.php';
$loggedIn = !empty($_SESSION['student_id']);
?>
<section class="grid lg:grid-cols-12 gap-14 items-center pt-4 pb-16 lg:pt-10 lg:pb-24">
  <div class="lg:col-span-7">
    <div class="mb-6 flex items-center gap-3">
      <img src="assets/img/logo.jpg" alt="Enugu State University of Science and Technology logo" class="h-14 w-auto">
      <p class="font-semibold leading-tight text-ink/80">Enugu State University<br>of Science and Technology</p>
    </div>
    <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-semibold tracking-tight leading-[0.98]">
      Register in minutes. Skip the queue.
    </h1>
    <p class="mt-6 text-lg text-ink/70 max-w-xl leading-relaxed">
      Create your account, choose your courses and print your slip from your phone. No paper forms and no waiting outside the registry.
    </p>
    <div class="mt-9 flex flex-wrap gap-3">
      <?php if ($loggedIn): ?>
        <a href="dashboard.php" class="btn btn-primary">Open your dashboard</a>
      <?php else: ?>
        <a href="register.php" class="btn btn-primary">Create your account</a>
        <a href="login.php" class="btn btn-ghost">I already have an account</a>
      <?php endif; ?>
    </div>
    <p class="mt-6 text-sm text-ink/60 max-w-md">Have these ready: your email address and a passport photograph (JPG or PNG, up to 2 MB).</p>
  </div>

  <div class="lg:col-span-5">
    <div class="relative mx-auto w-full max-w-sm" aria-hidden="true">
      <div class="absolute inset-0 translate-x-4 translate-y-5 -rotate-6 rounded-4xl bg-sun"></div>
      <article class="pass relative rounded-4xl bg-brand text-white p-6 shadow-2xl shadow-brand/30">
        <div class="mx-auto mb-6 h-2.5 w-16 rounded-full bg-white/30"></div>
        <div class="flex items-center justify-between text-sm font-semibold text-white/80">
          <span>Student pass</span><span><?= e(CURRENT_SESSION) ?></span>
        </div>
        <div class="mt-6 flex items-center gap-4">
          <div class="grid place-items-center w-20 h-24 rounded-2xl bg-white/15"><?= icon('user', 'w-9 h-9 text-white/80') ?></div>
          <div>
            <p class="font-display text-2xl font-semibold leading-tight">Udeh Iye Preciousfaith</p>
            <p class="mt-1 text-sm text-white/75">Computer Engineering</p>
          </div>
        </div>
        <div class="mt-6 flex items-center justify-between">
          <span class="chip chip-light">Approved</span>
          <span class="text-sm text-white/75">5 courses, 14 units</span>
        </div>
        <div class="barcode mt-6"></div>
      </article>
    </div>
  </div>
</section>

<section class="border-t border-line pt-12 pb-6" aria-labelledby="how">
  <h2 id="how" class="font-display text-3xl font-semibold tracking-tight">Three steps to a finished registration</h2>
  <ol class="mt-8 grid md:grid-cols-3 gap-8">
    <li class="flex gap-4">
      <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-ink text-white font-display font-semibold">1</span>
      <div><h3 class="font-semibold">Create your account</h3><p class="mt-1 text-ink/70">Enter your details, add your photo and choose a password.</p></div>
    </li>
    <li class="flex gap-4">
      <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-ink text-white font-display font-semibold">2</span>
      <div><h3 class="font-semibold">Choose your courses</h3><p class="mt-1 text-ink/70">Add the courses you are taking this session from your dashboard.</p></div>
    </li>
    <li class="flex gap-4">
      <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-ink text-white font-display font-semibold">3</span>
      <div><h3 class="font-semibold">Print your slip</h3><p class="mt-1 text-ink/70">Once the registry approves you, print your registration slip.</p></div>
    </li>
  </ol>
</section>

<section class="mt-12 flex flex-wrap gap-x-10 gap-y-4 text-sm font-semibold text-ink/70">
  <span class="flex items-center gap-2"><?= icon('phone2', 'w-5 h-5 text-brand') ?>Works on any phone or computer</span>
  <span class="flex items-center gap-2"><?= icon('shield', 'w-5 h-5 text-brand') ?>Passwords are stored hashed</span>
  <span class="flex items-center gap-2"><?= icon('clock', 'w-5 h-5 text-brand') ?>Check your status at any time</span>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
