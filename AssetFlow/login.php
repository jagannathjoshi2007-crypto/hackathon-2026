<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout-public.php';
render_public_header('Employee Login', 'login');
?>

<section class="auth-section">
  <main class="auth-card">
    <h1>Employee Login</h1>
    <p>Login to access your User Panel — view assets, book resources, and more.</p>
    <form id="loginForm">
      <label>Email</label>
      <input type="email" name="email" placeholder="you@company.com" required />
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required />
      <button type="submit" class="btn primary" style="width:100%;margin-top:16px">Login</button>
    </form>
    <p style="margin-top:16px"><a href="forgot-password.php">Forgot password?</a></p>
    <p>No account? <a href="signup.php"><strong>Sign up as Employee</strong></a></p>
    <p style="font-size:13px;color:#6b7280;margin-top:20px">
      Admin / Asset Manager? Use the same login — you'll be redirected to the Admin Panel automatically.
    </p>
  </main>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const res = await api('auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: fd.get('email'), password: fd.get('password') }),
    });
    const role = res.user?.role || 'employee';
    window.location.href = ['admin', 'asset_manager'].includes(role)
      ? 'admin/dashboard.php'
      : 'user/dashboard.php';
  } catch (err) {
    showToast(err.message, 'error');
  }
});
</script>
<?php render_public_footer(); ?>
