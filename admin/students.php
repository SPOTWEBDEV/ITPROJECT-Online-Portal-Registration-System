<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$statuses = ['pending', 'approved', 'rejected'];

// ---- Update a student's status ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['student_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id > 0 && in_array($status, $statuses, true)) {
        $stmt = $pdo->prepare('UPDATE students SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        flash('Status updated.');
    }
    header('Location: students.php?' . http_build_query(['q' => $_POST['q'] ?? '', 'status' => $_POST['filter'] ?? '']));
    exit;
}

// ---- Search and filter ----
$q = trim($_GET['q'] ?? '');
$filter = $_GET['status'] ?? '';
$sql = 'SELECT id, full_name, email, phone, department, status, created_at FROM students WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ? OR department LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if (in_array($filter, $statuses, true)) {
    $sql .= ' AND status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$counts = $pdo->query('SELECT status, COUNT(*) AS n FROM students GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($counts);
$flash = flash();

$tabs = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

$base = '../';
$title = 'Students';
include __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <h1 class="font-display text-4xl font-semibold tracking-tight">Students</h1>
    <p class="mt-1 text-ink/60"><?= (int)$total ?> registered, <?= (int)($counts['pending'] ?? 0) ?> waiting for review</p>
  </div>
  <form method="get" class="flex gap-2 w-full sm:w-auto" role="search">
    <input type="hidden" name="status" value="<?= e($filter) ?>">
    <div class="relative flex-1 sm:w-72">
      <span class="absolute left-4 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'w-5 h-5') ?></span>
      <input class="field field-icon" type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email or department" aria-label="Search students">
    </div>
    <button class="btn btn-primary">Search</button>
  </form>
</div>

<?php render_flash($flash); ?>

<nav class="flex flex-wrap gap-2 mb-5" aria-label="Filter by status">
  <?php foreach ($tabs as $key => $label):
      $n = $key === '' ? $total : (int)($counts[$key] ?? 0);
      $active = ($filter === $key) || ($key === '' && !in_array($filter, $statuses, true));
      $href = '?' . http_build_query(array_filter(['q' => $q, 'status' => $key]));
  ?>
    <a href="<?= e($href) ?>" class="rounded-full px-4 py-2 text-sm font-semibold border transition-colors <?= $active ? 'bg-ink text-white border-ink' : 'bg-white text-ink/70 border-line hover:border-ink' ?>"<?= $active ? ' aria-current="true"' : '' ?>>
      <?= e($label) ?> <span class="<?= $active ? 'text-white/70' : 'text-ink/40' ?>"><?= $n ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<section class="rounded-4xl bg-white border border-line overflow-hidden" aria-label="Student list">
  <div class="hidden md:grid md:grid-cols-[minmax(0,1.6fr)_minmax(0,1.2fr)_minmax(0,.8fr)_11rem] gap-4 px-6 py-3 text-xs font-semibold text-ink/50 bg-paper border-b border-line">
    <span>Student</span><span>Department</span><span>Registered</span><span>Status</span>
  </div>

  <?php if (!$students): ?>
    <div class="px-6 py-14 text-center">
      <span class="mx-auto grid place-items-center w-12 h-12 rounded-full bg-brand-soft text-brand"><?= icon('search', 'w-6 h-6') ?></span>
      <p class="mt-3 font-semibold">No students found</p>
      <p class="text-sm text-ink/60">Try a different search or choose another status.</p>
    </div>
  <?php endif; ?>

  <ul class="divide-y divide-line">
    <?php foreach ($students as $s): ?>
      <li class="grid gap-3 md:gap-4 md:grid-cols-[minmax(0,1.6fr)_minmax(0,1.2fr)_minmax(0,.8fr)_11rem] items-center px-6 py-4">
        <div class="flex items-center gap-3 min-w-0">
          <span class="grid place-items-center shrink-0 w-11 h-11 rounded-full bg-brand-soft text-brand font-display font-semibold"><?= e(initials($s['full_name'])) ?></span>
          <div class="min-w-0">
            <p class="font-semibold truncate"><?= e($s['full_name']) ?></p>
            <p class="text-sm text-ink/60 truncate"><?= e($s['email']) ?> &nbsp;<?= e($s['phone']) ?></p>
          </div>
        </div>
        <p class="text-sm truncate"><?= e($s['department']) ?></p>
        <p class="text-sm text-ink/60"><?= e(date('j M Y', strtotime($s['created_at']))) ?></p>
        <form method="post" class="flex items-center gap-2">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
          <input type="hidden" name="q" value="<?= e($q) ?>">
          <input type="hidden" name="filter" value="<?= e($filter) ?>">
          <select name="status" class="field field-sm" aria-label="Status for <?= e($s['full_name']) ?>" onchange="this.form.submit()">
            <?php foreach ($statuses as $st): ?>
              <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
          <noscript><button class="btn btn-ghost btn-sm">Save</button></noscript>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
