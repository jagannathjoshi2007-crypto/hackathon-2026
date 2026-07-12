<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout-public.php';
render_public_header('Employee Sign Up', 'signup');
?>

<section class="auth-section">
  <main class="auth-card">
    <h1>Create Employee Account</h1>
    <p>Sign up to access the User Panel. Accounts are created as <strong>Employee</strong> only — admin promotes roles later.</p>
    <form id="signupForm">
      <label>Full Name</label>
      <input type="text" name="name" placeholder="Your full name" required />
      <label>Email</label>
      <input type="email" name="email" placeholder="you@company.com" required />
      <label>Password</label>
      <input type="password" name="password" minlength="6" placeholder="Min 6 characters" required />
      <label>Department (optional)</label>
      <select name="department_id" id="departmentSelect">
        <option value="">Select department</option>
      </select>
      <button type="submit" class="btn primary" style="width:100%;margin-top:16px">Create Account</button>
    </form>
    <p style="margin-top:16px">Already registered? <a href="login.php"><strong>Login</strong></a></p>
  </main>
</section>

<script>
async function loadDepartments() {
  try {
    const res = await fetch('api/index.php?route=departments');
    const data = await res.json();
    const select = document.getElementById('departmentSelect');
    (data.departments || []).forEach((d) => {
      if (d.status === 'active') {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.name;
        select.appendChild(opt);
      }
    });
  } catch (_) {}
}
loadDepartments();

document.getElementById('signupForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('auth/signup', {
      method: 'POST',
      body: JSON.stringify({
        name: fd.get('name'),
        email: fd.get('email'),
        password: fd.get('password'),
        department_id: fd.get('department_id') || null,
      }),
    });
    showToast('Account created! Redirecting to login...');
    setTimeout(() => (window.location.href = 'login.php'), 900);
  } catch (err) {
    showToast(err.message, 'error');
  }
});
</script>
<?php render_public_footer(); ?>
