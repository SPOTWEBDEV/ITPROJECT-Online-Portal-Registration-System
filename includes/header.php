<?php
// Expects $title and $base ('' for root pages, '../' for pages inside /admin)
require_once __DIR__ . '/ui.php';
$base    = $base ?? '';
$title   = $title ?? 'Student Portal';
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

function nav_link(string $href, string $label, array $files, string $current, string $base, string $extra = ''): string {
    $active = in_array($current, $files, true);
    $cls = $active ? 'bg-ink text-white' : 'text-ink/70 hover:text-ink hover:bg-ink/5';
    return '<a href="' . $base . $href . '" class="rounded-full px-4 py-2 transition-colors ' . $cls . ' ' . $extra . '"'
         . ($active ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | Student Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Bricolage Grotesque"', 'system-ui', 'sans-serif'],
            sans: ['Figtree', 'system-ui', 'sans-serif']
          },
          colors: {
            ink: '#1A2140', paper: '#F4F6FB', line: '#E2E6F1',
            brand: { DEFAULT: '#3342E8', dark: '#2433C9', soft: '#E8EBFF' },
            sun: '#FFD23F', ok: '#0E9F5B', bad: '#D92D20'
          },
          borderRadius: { '4xl': '2rem' }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="<?= $base ?>assets/css/portal.css">
</head>
<body class="min-h-screen flex flex-col bg-paper text-ink font-sans antialiased">
<a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded-full">Skip to content</a>

<header class="sticky top-3 z-40 px-3 no-print">
  <div class="mx-auto max-w-6xl flex items-center justify-between rounded-full border border-line bg-white/85 backdrop-blur pl-3 pr-2 py-2 shadow-sm">
    <a href="<?= $base ?>index.php" class="flex items-center gap-2.5">
      <span class="grid place-items-center w-10 h-10 rounded-full bg-brand text-white"><?= icon('cap', 'w-5 h-5') ?></span>
      <span class="font-display text-lg font-semibold tracking-tight">Student Portal</span>
    </a>
    <nav class="flex items-center gap-1 text-sm font-semibold" aria-label="Main">
      <?php if (!empty($_SESSION['student_id'])): ?>
        <?= nav_link('dashboard.php', 'Dashboard', ['dashboard.php', 'slip.php'], $current, $base) ?>
        <a href="<?= $base ?>logout.php" class="flex items-center gap-2 rounded-full px-4 py-2 text-ink/70 hover:text-ink hover:bg-ink/5 transition-colors">
          <?= icon('logout', 'w-4 h-4') ?><span>Log out</span>
        </a>
      <?php elseif (!empty($_SESSION['admin_id'])): ?>
        <?= nav_link('admin/students.php', 'Students', ['students.php'], $current, $base) ?>
        <?= nav_link('admin/courses.php', 'Courses', ['courses.php'], $current, $base) ?>
        <a href="<?= $base ?>admin/logout.php" class="flex items-center gap-2 rounded-full px-4 py-2 text-ink/70 hover:text-ink hover:bg-ink/5 transition-colors">
          <?= icon('logout', 'w-4 h-4') ?><span>Log out</span>
        </a>
      <?php else: ?>
        <?= nav_link('index.php', 'Home', ['index.php'], $current, $base, 'hidden sm:inline-block') ?>
        <?= nav_link('login.php', 'Log in', ['login.php'], $current, $base) ?>
        <a href="<?= $base ?>register.php" class="btn btn-primary btn-sm ml-1">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="content" class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-12">
