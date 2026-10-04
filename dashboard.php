<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require_student();

$sid = (int)$_SESSION['student_id'];

// ---- Add or remove a course ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $courseId = (int)($_POST['course_id'] ?? 0);
        $check = $pdo->prepare('SELECT id FROM courses WHERE id = ?');
        $check->execute([$courseId]);
        if (!$check->fetch()) {
            flash('Choose a valid course from the list.', 'error');
        } else {
            $dup = $pdo->prepare('SELECT id FROM course_registrations WHERE student_id = ? AND course_id = ? AND session = ?');
            $dup->execute([$sid, $courseId, CURRENT_SESSION]);
            if ($dup->fetch()) {
                flash('You have already registered that course.', 'error');
            } else {
                $ins = $pdo->prepare('INSERT INTO course_registrations (student_id, course_id, session) VALUES (?, ?, ?)');
                $ins->execute([$sid, $courseId, CURRENT_SESSION]);
                flash('Course added to your registration.');
            }
        }
    } elseif ($action === 'remove') {
        $regId = (int)($_POST['reg_id'] ?? 0);
        $del = $pdo->prepare('DELETE FROM course_registrations WHERE id = ? AND student_id = ?');
        $del->execute([$regId, $sid]);
        flash('Course removed.');
    }
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$sid]);
$student = $stmt->fetch();
if (!$student) {            // account was deleted by an administrator
    header('Location: logout.php');
    exit;
}

$registered = $pdo->prepare(
    'SELECT cr.id, c.course_code, c.course_title, c.unit
     FROM course_registrations cr JOIN courses c ON c.id = cr.course_id
     WHERE cr.student_id = ? AND cr.session = ? ORDER BY c.course_code'
);
$registered->execute([$sid, CURRENT_SESSION]);
$myCourses = $registered->fetchAll();
$totalUnits = array_sum(array_column($myCourses, 'unit'));

$available = $pdo->prepare(
    'SELECT id, course_code, course_title, unit FROM courses
     WHERE id NOT IN (SELECT course_id FROM course_registrations WHERE student_id = ? AND session = ?)
     ORDER BY course_code'
);
$available->execute([$sid, CURRENT_SESSION]);
$availableCourses = $available->fetchAll();

$flash = flash();
$status = $student['status'];
$firstName = explode(' ', trim($student['full_name']))[0];
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

$timeline = [
    ['Account created', 'Your details were received.', 'done'],
    ['Under review', 'The registry is checking your details.', $status === 'pending' ? 'current' : 'done'],
    [
        $status === 'rejected' ? 'Not approved' : 'Approved',
        $status === 'rejected' ? 'Contact the registry to find out why.' : 'You are cleared for the ' . CURRENT_SESSION . ' session.',
        $status === 'pending' ? 'todo' : ($status === 'rejected' ? 'bad' : 'done'),
    ],
];
$dotClass = ['done' => 'bg-ok text-white', 'current' => 'bg-sun text-ink', 'todo' => 'bg-line text-ink/40', 'bad' => 'bg-bad text-white'];
$dotIcon  = ['done' => 'check', 'current' => 'clock', 'todo' => 'clock', 'bad' => 'x'];

$title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<?php render_flash($flash); ?>

<div class="mb-8">
  <h1 class="font-display text-4xl sm:text-5xl font-semibold tracking-tight"><?= e($greeting) ?>, <?= e($firstName) ?></h1>
  <p class="mt-2 text-ink/60"><?= e(date('l, j F Y')) ?></p>
</div>

<div class="grid lg:grid-cols-12 gap-6 items-start">

  <!-- Left column: pass and status -->
  <div class="lg:col-span-4 grid gap-6">
    <article class="pass pass-flat rounded-4xl bg-brand text-white p-6 shadow-xl shadow-brand/20">
      <div class="mx-auto mb-5 h-2.5 w-16 rounded-full bg-white/30"></div>
      <div class="flex items-center justify-between text-sm font-semibold text-white/80">
        <span>Student pass</span><span><?= e(CURRENT_SESSION) ?></span>
      </div>
      <div class="mt-5 flex items-center gap-4">
        <img class="w-20 h-24 rounded-2xl object-cover bg-white/15" src="assets/uploads/<?= e($student['passport_photo']) ?>" alt="Your passport photograph">
        <div class="min-w-0">
          <p class="font-display text-2xl font-semibold leading-tight break-words"><?= e($student['full_name']) ?></p>
          <p class="mt-1 text-sm text-white/75"><?= e($student['department']) ?></p>
        </div>
      </div>
      <div class="mt-5 flex items-center justify-between">
        <span class="chip chip-light"><?= e(ucfirst($status)) ?></span>
        <span class="text-sm text-white/75"><?= count($myCourses) ?> course<?= count($myCourses) === 1 ? '' : 's' ?>, <?= (int)$totalUnits ?> units</span>
      </div>
      <div class="barcode mt-5"></div>
    </article>

    <section class="rounded-4xl bg-white border border-line p-6" aria-labelledby="status-title">
      <h2 id="status-title" class="font-display text-xl font-semibold">Registration status</h2>
      <ol class="mt-5">
        <?php foreach ($timeline as $i => [$label, $note, $state]): ?>
          <li class="flex gap-4">
            <div class="flex flex-col items-center">
              <span class="grid place-items-center w-8 h-8 rounded-full <?= $dotClass[$state] ?>"><?= icon($dotIcon[$state], 'w-4 h-4') ?></span>
              <?php if ($i < count($timeline) - 1): ?><span class="w-0.5 flex-1 min-h-6 bg-line"></span><?php endif; ?>
            </div>
            <div class="pb-5">
              <p class="font-semibold leading-8"><?= e($label) ?></p>
              <p class="text-sm text-ink/60"><?= e($note) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>
  </div>

  <!-- Right column: details and courses -->
  <div class="lg:col-span-8 grid gap-6">

    <section class="rounded-4xl bg-white border border-line p-6 sm:p-8" aria-labelledby="details-title">
      <h2 id="details-title" class="font-display text-xl font-semibold">Your details</h2>
      <dl class="mt-5 grid sm:grid-cols-2 gap-x-8 gap-y-5">
        <?php
        $details = [
            ['mail', 'Email', $student['email']],
            ['phone', 'Phone', $student['phone']],
            ['calendar', 'Date of birth', date('j F Y', strtotime($student['dob']))],
            ['user', 'Gender', $student['gender']],
            ['building', 'Department', $student['department']],
            ['clock', 'Registered on', date('j F Y', strtotime($student['created_at']))],
        ];
        foreach ($details as [$ic, $lab, $value]): ?>
          <div class="flex items-center gap-3 min-w-0">
            <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-brand-soft text-brand"><?= icon($ic, 'w-5 h-5') ?></span>
            <div class="min-w-0"><dt class="text-xs text-ink/50"><?= e($lab) ?></dt><dd class="font-semibold truncate"><?= e($value) ?></dd></div>
          </div>
        <?php endforeach; ?>
      </dl>
    </section>

    <section class="rounded-4xl bg-white border border-line p-6 sm:p-8" aria-labelledby="courses-title">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 id="courses-title" class="font-display text-xl font-semibold">Courses for <?= e(CURRENT_SESSION) ?></h2>
          <p class="text-sm text-ink/60"><?= count($myCourses) ?> registered, <?= (int)$totalUnits ?> units in total</p>
        </div>
        <a href="slip.php" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><?= icon('printer', 'w-4 h-4') ?>Print slip</a>
      </div>

      <form method="post" class="mt-6 flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <select name="course_id" class="field flex-1" aria-label="Choose a course to add" <?= $availableCourses ? '' : 'disabled' ?>>
          <option value=""><?= $availableCourses ? 'Choose a course to add' : 'You have registered every available course' ?></option>
          <?php foreach ($availableCourses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['course_code'] . ': ' . $c['course_title'] . ' (' . $c['unit'] . ' units)') ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" <?= $availableCourses ? '' : 'disabled' ?>><?= icon('plus', 'w-4 h-4') ?>Add course</button>
      </form>

      <?php if (!$myCourses): ?>
        <div class="mt-6 rounded-3xl border border-dashed border-line px-6 py-10 text-center">
          <span class="mx-auto grid place-items-center w-12 h-12 rounded-full bg-brand-soft text-brand"><?= icon('book', 'w-6 h-6') ?></span>
          <p class="mt-3 font-semibold">No courses yet</p>
          <p class="text-sm text-ink/60">Pick a course from the list above to start your registration.</p>
        </div>
      <?php else: ?>
        <ul class="mt-6 divide-y divide-line">
          <?php foreach ($myCourses as $c): ?>
            <li class="flex items-center gap-4 py-4">
              <span class="shrink-0 rounded-lg bg-brand-soft text-brand text-sm font-semibold px-3 py-1.5"><?= e($c['course_code']) ?></span>
              <span class="flex-1 min-w-0 font-medium"><?= e($c['course_title']) ?></span>
              <span class="text-sm text-ink/60 shrink-0"><?= (int)$c['unit'] ?> units</span>
              <form method="post" class="shrink-0" onsubmit="return confirm('Remove this course from your registration?');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="reg_id" value="<?= (int)$c['id'] ?>">
                <button class="grid place-items-center w-9 h-9 rounded-full text-ink/50 hover:text-bad hover:bg-bad/10 transition-colors" aria-label="Remove <?= e($c['course_code']) ?>">
                  <?= icon('trash', 'w-4 h-4') ?>
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
