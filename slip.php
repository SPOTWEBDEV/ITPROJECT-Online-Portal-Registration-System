<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require_student();

$sid = (int)$_SESSION['student_id'];
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$sid]);
$student = $stmt->fetch();
if (!$student) { header('Location: logout.php'); exit; }

$c = $pdo->prepare(
    'SELECT c.course_code, c.course_title, c.unit
     FROM course_registrations cr JOIN courses c ON c.id = cr.course_id
     WHERE cr.student_id = ? AND cr.session = ? ORDER BY c.course_code'
);
$c->execute([$sid, CURRENT_SESSION]);
$courses = $c->fetchAll();
$total = array_sum(array_column($courses, 'unit'));

$title = 'Registration slip';
include __DIR__ . '/includes/header.php';
?>
<div class="mx-auto max-w-3xl">
  <div class="no-print mb-5 flex flex-wrap items-center justify-between gap-3">
    <a href="dashboard.php" class="flex items-center gap-1 text-sm font-semibold text-ink/70 hover:text-ink"><?= icon('back', 'w-4 h-4') ?>Back to dashboard</a>
    <button onclick="window.print()" class="btn btn-primary btn-sm"><?= icon('printer', 'w-4 h-4') ?>Print slip</button>
  </div>

  <article class="rounded-4xl bg-white border border-line p-8 sm:p-10 print:rounded-none print:border-2 print:border-black">
    <header class="flex items-start justify-between gap-6 border-b border-line pb-6">
      <div>
        <div class="flex items-center gap-3"><img src="assets/img/logo.jpg" alt="Enugu State University of Science and Technology logo" class="h-12 w-auto"><p class="font-display text-base font-semibold leading-tight">Enugu State University<br>of Science and Technology</p></div>
        <h1 class="mt-4 font-display text-3xl sm:text-4xl font-semibold tracking-tight">Registration slip</h1>
        <p class="mt-1 text-ink/60">Session <?= e(CURRENT_SESSION) ?></p>
      </div>
      <img class="w-24 h-28 rounded-2xl object-cover border border-line shrink-0" src="assets/uploads/<?= e($student['passport_photo']) ?>" alt="Passport photograph">
    </header>

    <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-4 py-6 text-sm">
      <div><dt class="text-ink/50">Full name</dt><dd class="font-semibold text-base"><?= e($student['full_name']) ?></dd></div>
      <div><dt class="text-ink/50">Department</dt><dd class="font-semibold text-base"><?= e($student['department']) ?></dd></div>
      <div><dt class="text-ink/50">Email</dt><dd class="font-semibold"><?= e($student['email']) ?></dd></div>
      <div><dt class="text-ink/50">Phone</dt><dd class="font-semibold"><?= e($student['phone']) ?></dd></div>
      <div><dt class="text-ink/50">Registration status</dt><dd class="mt-1"><?= status_chip($student['status']) ?></dd></div>
    </dl>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead>
          <tr class="border-y border-line text-ink/60">
            <th class="py-3 pr-4 font-semibold">Code</th><th class="py-3 pr-4 font-semibold">Course title</th><th class="py-3 font-semibold text-right">Units</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$courses): ?>
            <tr><td colspan="3" class="py-6 text-center text-ink/60">No courses registered for this session yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($courses as $row): ?>
            <tr class="border-b border-line">
              <td class="py-3 pr-4 font-semibold"><?= e($row['course_code']) ?></td>
              <td class="py-3 pr-4"><?= e($row['course_title']) ?></td>
              <td class="py-3 text-right"><?= (int)$row['unit'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td class="pt-4 font-semibold" colspan="2">Total units</td><td class="pt-4 text-right font-semibold"><?= (int)$total ?></td></tr>
        </tfoot>
      </table>
    </div>

    <footer class="mt-10 grid grid-cols-2 gap-8 text-xs text-ink/60">
      <div><div class="border-t border-ink/30 pt-2">Student signature</div></div>
      <div><div class="border-t border-ink/30 pt-2">Registry stamp</div></div>
    </footer>
    <p class="mt-6 text-xs text-ink/50">Printed on <?= e(date('j F Y, H:i')) ?></p>
  </article>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
