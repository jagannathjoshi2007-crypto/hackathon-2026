<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout-public.php';
require_once __DIR__ . '/config/app.php';
render_public_header('Forgot Password', 'login');
?>

<section class="auth-section">
  <main class="auth-card">
    <h1>Forgot Password</h1>
    <p>Enter your email, the <strong>static reset hash</strong>, and your new password.</p>

    <form id="forgotForm">
      <label>Email</label>
      <input type="email" name="email" placeholder="you@company.com" required />

      <label>Reset Hash</label>
      <input type="text" name="hash" placeholder="Enter static reset hash" required />
      <p class="hint">Demo hash for this project: <code><?= htmlspecialchars(RESET_HASH) ?></code></p>

      <label>New Password</label>
      <input type="password" name="password" minlength="6" placeholder="Min 6 characters" required />

      <label>Confirm New Password</label>
      <input type="password" name="password_confirm" minlength="6" placeholder="Re-enter password" required />

      <button type="submit" class="btn primary" style="width:100%;margin-top:16px">Change Password</button>
    </form>

    <p style="margin-top:16px"><a href="login.php">Back to Login</a></p>
  </main>
</section>

<script>
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('auth/reset', {
      method: 'POST',
      body: JSON.stringify({
        email: fd.get('email'),
        hash: fd.get('hash'),
        password: fd.get('password'),
        password_confirm: fd.get('password_confirm'),
      }),
    });
    showToast('Password changed! Redirecting to login...');
    setTimeout(() => (window.location.href = 'login.php'), 1200);
  } catch (err) {
    showToast(err.message, 'error');
  }
});
</script>
<?php render_public_footer(); ?>
