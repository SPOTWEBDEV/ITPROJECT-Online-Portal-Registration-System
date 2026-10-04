<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code = strtoupper(trim($_POST['course_code'] ?? ''));
        $ctitle = trim($_POST['course_title'] ?? '');
        $unit = (int)($_POST['unit'] ?? 0);
        if (!preg_match('/^[A-Z]{2,5}[0-9]{2,4}$/', $code)) {
            $errors[] = 'Course code should look like CSC101.';
        }
        if ($ctitle === '' || mb_strlen($ctitle) > 150) {
            $errors[] = 'Enter a course title (up to 150 characters).';
        }
        if ($unit < 1 || $unit > 6) {
            $errors[] = 'Units must be between 1 and 6.';
        }
        if (!$errors) {
            $dup = $pdo->prepare('SELECT id FROM courses WHERE course_code = ?');
            $dup->execute([$code]);
            if ($dup->fetch()) {
                $errors[] = 'That course code already exists.';
            } else {
                $ins = $pdo->prepare('INSERT INTO courses (course_code, course_title, unit) VALUES (?, ?, ?)');
                $ins->execute([$code, $ctitle, $unit]);
                flash('Course added.');
                header('Location: courses.php');
                exit;
            }
        }
    } elseif ($action === 'delete') {
        $del = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $del->execute([(int)($_POST['course_id'] ?? 0)]);
        flash('Course deleted.');
        header('Location: courses.php');
        exit;
    }
}

$courses = $pdo->query('SELECT id, course_code, course_title, unit FROM courses ORDER BY course_code')->fetchAll();
$flash = flash();

$base = '../';
$title = 'Courses';
include __DIR__ . '/../includes/header.php';
?>
<div class="mb-6">
  <h1 class="font-display text-4xl font-semibold tracking-tight">Courses</h1>
  <p class="mt-1 text-ink/60"><?= count($courses) ?> available to students this session</p>
</div>

<?php render_flash($flash); ?>
<?php foreach ($errors as $err): ?>
  <?php render_flash(['message' => $err, 'type' => 'error']); ?>
<?php endforeach; ?>

<section class="rounded-4xl bg-white border border-line p-6 sm:p-8 mb-6" aria-labelledby="add-title">
  <h2 id="add-title" class="font-display text-xl font-semibold">Add a course</h2>
  <form method="post" class="mt-5 grid gap-4 md:grid-cols-[9rem_minmax(0,1fr)_7rem_auto] items-end">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="add">
    <div><label class="label" for="course_code">Code</label><input class="field" id="course_code" name="course_code" placeholder="CSC101" required></div>
    <div><label class="label" for="course_title">Title</label><input class="field" id="course_title" name="course_title" placeholder="Introduction to Computer Science" required></div>
    <div><label class="label" for="unit">Units</label><input class="field" type="number" id="unit" name="unit" min="1" max="6" placeholder="3" required></div>
    <button class="btn btn-primary"><?= icon('plus', 'w-4 h-4') ?>Add course</button>
  </form>
</section>

<section class="rounded-4xl bg-white border border-line p-6 sm:p-8" aria-labelledby="list-title">
  <h2 id="list-title" class="font-display text-xl font-semibold">Course list</h2>
  <?php if (!$courses): ?>
    <div class="mt-6 rounded-3xl border border-dashed border-line px-6 py-10 text-center">
      <span class="mx-auto grid place-items-center w-12 h-12 rounded-full bg-brand-soft text-brand"><?= icon('book', 'w-6 h-6') ?></span>
      <p class="mt-3 font-semibold">No courses yet</p>
      <p class="text-sm text-ink/60">Add your first course with the form above.</p>
    </div>
  <?php else: ?>
    <ul class="mt-4 divide-y divide-line">
      <?php foreach ($courses as $c): ?>
        <li class="flex items-center gap-4 py-4">
          <span class="shrink-0 rounded-lg bg-brand-soft text-brand text-sm font-semibold px-3 py-1.5"><?= e($c['course_code']) ?></span>
          <span class="flex-1 min-w-0 font-medium"><?= e($c['course_title']) ?></span>
          <span class="text-sm text-ink/60 shrink-0"><?= (int)$c['unit'] ?> units</span>
          <form method="post" class="shrink-0" onsubmit="return confirm('Delete this course? Student registrations for it will also be removed.');">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
            <button class="grid place-items-center w-9 h-9 rounded-full text-ink/50 hover:text-bad hover:bg-bad/10 transition-colors" aria-label="Delete <?= e($c['course_code']) ?>">
              <?= icon('trash', 'w-4 h-4') ?>
            </button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
