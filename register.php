<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$departments = ['Computer Engineering', 'Computer Science', 'Electrical Engineering', 'Mechanical Engineering', 'Business Administration', 'Accounting'];
$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'dob' => '', 'gender' => '', 'department' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    foreach ($old as $key => $_) {
        $old[$key] = trim($_POST[$key] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Server-side validation (never trust the browser alone) ----
    if (mb_strlen($old['full_name']) < 3 || mb_strlen($old['full_name']) > 100) {
        $errors['full_name'] = 'Enter your full name (3 to 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $old['phone'])) {
        $errors['phone'] = 'Enter a valid phone number.';
    }
    $d = DateTime::createFromFormat('Y-m-d', $old['dob']);
    if (!$d || $d->format('Y-m-d') !== $old['dob'] || $d >= new DateTime('today')) {
        $errors['dob'] = 'Enter a valid date of birth.';
    }
    if (!in_array($old['gender'], ['Male', 'Female'], true)) {
        $errors['gender'] = 'Select your gender.';
    }
    if (!in_array($old['department'], $departments, true)) {
        $errors['department'] = 'Select your department.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // ---- Passport photograph ----
    $photo = $_FILES['passport_photo'] ?? null;
    $ext = null;
    if (!$photo || $photo['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['passport_photo'] = 'Please upload a passport photograph.';
    } elseif ($photo['error'] !== UPLOAD_ERR_OK) {
        $errors['passport_photo'] = 'The photograph could not be uploaded. Try a smaller file.';
    } elseif ($photo['size'] > 2 * 1024 * 1024) {
        $errors['passport_photo'] = 'The photograph must not be larger than 2 MB.';
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($photo['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (!isset($allowed[$mime]) || @getimagesize($photo['tmp_name']) === false) {
            $errors['passport_photo'] = 'Only JPG or PNG images are allowed.';
        } else {
            $ext = $allowed[$mime];
        }
    }

    // ---- Email must be unique ----
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare('SELECT id FROM students WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email address is already registered.';
        }
    }

    // ---- Save ----
    if (!$errors) {
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($photo['tmp_name'], __DIR__ . '/assets/uploads/' . $filename)) {
            $errors['passport_photo'] = 'The photograph could not be saved.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO students (full_name, email, phone, dob, gender, department, passport_photo, password)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $old['full_name'], $old['email'], $old['phone'], $old['dob'],
                    $old['gender'], $old['department'], $filename,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $ex) {
                @unlink(__DIR__ . '/assets/uploads/' . $filename);
                $errors['email'] = ($ex->getCode() === '23000')
                    ? 'This email address is already registered.'
                    : 'Something went wrong. Please try again.';
            }
        }
    }
}

// Which step should open first? The first one that contains an error.
$stepOf = [
    'full_name' => 1, 'email' => 1, 'phone' => 1, 'dob' => 1, 'gender' => 1,
    'department' => 2, 'passport_photo' => 2,
    'password' => 3, 'confirm_password' => 3,
];
$startStep = 1;
if ($errors) {
    $startStep = 3;
    foreach ($errors as $field => $_msg) {
        $startStep = min($startStep, $stepOf[$field] ?? 1);
    }
}
$invalid = fn(string $f): string => isset($errors[$f]) ? ' aria-invalid="true"' : '';

$title = 'Create your account';
include __DIR__ . '/includes/header.php';
?>
<div class="grid lg:grid-cols-12 gap-8 items-start">

  <!-- Left: what you need -->
  <aside class="lg:col-span-4 lg:sticky lg:top-28 rounded-4xl bg-brand text-white p-8">
    <h1 class="font-display text-4xl font-semibold tracking-tight leading-tight">Create your student account</h1>
    <p class="mt-4 text-white/80">It takes about three minutes. You can log in as soon as you finish.</p>
    <ul class="mt-8 grid gap-4 text-sm">
      <li class="flex gap-3"><span class="grid place-items-center shrink-0 w-6 h-6 rounded-full bg-white/20"><?= icon('check', 'w-4 h-4') ?></span>A passport photograph (JPG or PNG, up to 2 MB)</li>
      <li class="flex gap-3"><span class="grid place-items-center shrink-0 w-6 h-6 rounded-full bg-white/20"><?= icon('check', 'w-4 h-4') ?></span>An email address you can sign in with</li>
      <li class="flex gap-3"><span class="grid place-items-center shrink-0 w-6 h-6 rounded-full bg-white/20"><?= icon('check', 'w-4 h-4') ?></span>Your department</li>
    </ul>
    <p class="mt-10 text-sm text-white/80">Already registered? <a class="font-semibold text-white underline underline-offset-4" href="login.php">Log in</a></p>
  </aside>

  <!-- Right: the form -->
  <section class="lg:col-span-8 rounded-4xl bg-white border border-line p-6 sm:p-10" aria-labelledby="form-title">
    <ol class="grid grid-cols-3 gap-3 mb-8" aria-label="Progress">
      <li class="step-ind is-active" data-step-indicator="1"><span class="bar"></span><span class="name">About you</span></li>
      <li class="step-ind" data-step-indicator="2"><span class="bar"></span><span class="name">School details</span></li>
      <li class="step-ind" data-step-indicator="3"><span class="bar"></span><span class="name">Password</span></li>
    </ol>

    <?php if ($errors): ?>
      <div role="alert" class="flex gap-3 rounded-2xl border border-bad/30 bg-bad/10 text-bad px-4 py-3 text-sm font-semibold mb-6">
        <?= icon('alert', 'w-5 h-5 shrink-0') ?>
        <span>Fix the highlighted fields to continue. You will need to choose your photograph again.</span>
      </div>
    <?php endif; ?>

    <form id="register-form" method="post" enctype="multipart/form-data" novalidate data-start-step="<?= (int)$startStep ?>">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <!-- Step 1 -->
      <div data-step="1" class="grid gap-5 sm:grid-cols-2">
        <h2 id="form-title" class="font-display text-2xl font-semibold sm:col-span-2">Tell us about yourself</h2>

        <div class="sm:col-span-2">
          <label class="label" for="full_name">Full name</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('user', 'w-5 h-5') ?></span>
            <input class="field field-icon" type="text" id="full_name" name="full_name" autocomplete="name" placeholder="As it appears on your documents" value="<?= e($old['full_name']) ?>"<?= $invalid('full_name') ?>>
          </div>
          <p class="error" data-error-for="full_name"><?= e($errors['full_name'] ?? '') ?></p>
        </div>

        <div>
          <label class="label" for="email">Email address</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('mail', 'w-5 h-5') ?></span>
            <input class="field field-icon" type="email" id="email" name="email" autocomplete="email" placeholder="you@example.com" value="<?= e($old['email']) ?>"<?= $invalid('email') ?>>
          </div>
          <p class="error" data-error-for="email"><?= e($errors['email'] ?? '') ?></p>
        </div>

        <div>
          <label class="label" for="phone">Phone number</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('phone', 'w-5 h-5') ?></span>
            <input class="field field-icon" type="tel" id="phone" name="phone" autocomplete="tel" placeholder="0801 234 5678" value="<?= e($old['phone']) ?>"<?= $invalid('phone') ?>>
          </div>
          <p class="error" data-error-for="phone"><?= e($errors['phone'] ?? '') ?></p>
        </div>

        <div>
          <label class="label" for="dob">Date of birth</label>
          <input class="field" type="date" id="dob" name="dob" autocomplete="bday" value="<?= e($old['dob']) ?>"<?= $invalid('dob') ?>>
          <p class="error" data-error-for="dob"><?= e($errors['dob'] ?? '') ?></p>
        </div>

        <fieldset>
          <legend class="label">Gender</legend>
          <div class="grid grid-cols-2 gap-3">
            <?php foreach (['Male', 'Female'] as $g): ?>
              <label class="radio-pill">
                <input class="sr-only" type="radio" name="gender" value="<?= $g ?>" <?= $old['gender'] === $g ? 'checked' : '' ?>>
                <span><?= $g ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="error" data-error-for="gender"><?= e($errors['gender'] ?? '') ?></p>
        </fieldset>

        <div class="step-nav sm:col-span-2 justify-end pt-2">
          <button type="button" class="btn btn-primary" data-next>Continue</button>
        </div>
      </div>

      <!-- Step 2 -->
      <div data-step="2" class="grid gap-5">
        <h2 class="font-display text-2xl font-semibold">Your school details</h2>

        <div>
          <label class="label" for="department">Department</label>
          <select class="field" id="department" name="department"<?= $invalid('department') ?>>
            <option value="">Choose your department</option>
            <?php foreach ($departments as $dep): ?>
              <option value="<?= e($dep) ?>" <?= $old['department'] === $dep ? 'selected' : '' ?>><?= e($dep) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="error" data-error-for="department"><?= e($errors['department'] ?? '') ?></p>
        </div>

        <div>
          <span class="label">Passport photograph</span>
          <label for="passport_photo" class="dropzone">
            <span class="grid place-items-center shrink-0 w-20 h-24 rounded-2xl bg-brand-soft text-brand overflow-hidden">
              <img id="photo_preview" class="hidden w-full h-full object-cover" alt="Preview of your photograph">
              <span id="photo_placeholder"><?= icon('image', 'w-8 h-8') ?></span>
            </span>
            <span class="grid gap-0.5 text-sm">
              <strong class="text-base">Choose a photograph</strong>
              <span class="text-ink/60">JPG or PNG, up to 2 MB. A clear face on a plain background works best.</span>
              <span class="text-brand font-semibold" data-file-name></span>
            </span>
            <input class="sr-only" type="file" id="passport_photo" name="passport_photo" accept="image/jpeg,image/png"<?= $invalid('passport_photo') ?>>
          </label>
          <p class="error" data-error-for="passport_photo"><?= e($errors['passport_photo'] ?? '') ?></p>
        </div>

        <div class="step-nav justify-between pt-2">
          <button type="button" class="btn btn-ghost" data-prev><?= icon('back', 'w-4 h-4') ?>Back</button>
          <button type="button" class="btn btn-primary" data-next>Continue</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div data-step="3" class="grid gap-5 sm:grid-cols-2">
        <h2 class="font-display text-2xl font-semibold sm:col-span-2">Choose a password</h2>

        <div class="sm:col-span-2">
          <label class="label" for="password">Password</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('lock', 'w-5 h-5') ?></span>
            <input class="field field-icon" type="password" data-password id="password" name="password" autocomplete="new-password" placeholder="At least 8 characters"<?= $invalid('password') ?>>
          </div>
          <div class="mt-3 flex items-center gap-3" data-strength aria-live="polite">
            <div class="flex flex-1 gap-1.5"><span class="sbar"></span><span class="sbar"></span><span class="sbar"></span><span class="sbar"></span></div>
            <span class="text-xs font-semibold text-ink/60 w-14 text-right" data-strength-label></span>
          </div>
          <p class="error" data-error-for="password"><?= e($errors['password'] ?? '') ?></p>
        </div>

        <div class="sm:col-span-2">
          <label class="label" for="confirm_password">Confirm password</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('lock', 'w-5 h-5') ?></span>
            <input class="field field-icon" type="password" data-password id="confirm_password" name="confirm_password" autocomplete="new-password" placeholder="Type it again"<?= $invalid('confirm_password') ?>>
          </div>
          <p class="error" data-error-for="confirm_password"><?= e($errors['confirm_password'] ?? '') ?></p>
        </div>

        <label class="sm:col-span-2 flex items-center gap-2.5 text-sm font-medium cursor-pointer">
          <input type="checkbox" class="w-4 h-4 accent-[#3342E8]" data-toggle-password> Show passwords
        </label>

        <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-3 pt-2">
          <button type="button" class="btn btn-ghost step-nav" data-prev><?= icon('back', 'w-4 h-4') ?>Back</button>
          <button type="submit" class="btn btn-primary w-full sm:w-auto">Create account</button>
        </div>
      </div>
    </form>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
