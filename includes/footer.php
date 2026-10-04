</main>
<footer class="no-print border-t border-line">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex flex-wrap items-center justify-between gap-3 text-sm text-ink/60">
    <p>Student Portal. Session <?= e(CURRENT_SESSION) ?>.</p>
    <?php if (empty($_SESSION['admin_id']) && empty($_SESSION['student_id'])): ?>
      <a class="font-semibold text-ink/70 hover:text-ink" href="<?= $base ?? '' ?>admin/login.php">Staff login</a>
    <?php endif; ?>
  </div>
</footer>
<script src="<?= $base ?? '' ?>assets/js/validate.js"></script>
</body>
</html>
