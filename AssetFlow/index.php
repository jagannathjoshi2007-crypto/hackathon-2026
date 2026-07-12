<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout-public.php';
$user = current_user();
render_public_header('Home', 'home');
?>

<section class="hero">
  <div class="hero-content">
    <span class="hero-badge">Enterprise Asset Management</span>
    <h1>Track, allocate, and manage every company asset in one place</h1>
    <p>
      AssetFlow helps teams register assets, prevent allocation conflicts, book shared resources,
      run maintenance workflows, and close audit cycles — built for the Odoo Hackathon 2026.
    </p>
    <div class="hero-actions">
      <?php if ($user): ?>
        <?php if (is_admin_role($user['role'])): ?>
        <a class="btn primary large" href="admin/dashboard.php">Open Admin Panel</a>
        <?php else: ?>
        <a class="btn primary large" href="user/dashboard.php">Open My Dashboard</a>
        <?php endif; ?>
      <?php else: ?>
        <a class="btn primary large" href="signup.php">Create Employee Account</a>
        <a class="btn secondary large" href="login.php">Employee Login</a>
      <?php endif; ?>
      <a class="btn secondary large" href="#features">Explore Features</a>
    </div>
  </div>
  <div class="card hero-card">
    <h3>Get started in 3 steps</h3>
    <ol class="steps-list">
      <li>Start <strong>Apache</strong> and <strong>MySQL</strong> in XAMPP</li>
      <li>Run <strong>install.php</strong> once to set up the database</li>
      <li>Login as <strong>admin@assetflow.com</strong> / <strong>admin123</strong></li>
    </ol>
  </div>
</section>

<section class="stats-strip">
  <div><strong>7</strong><span>Asset lifecycle states</span></div>
  <div><strong>4</strong><span>User roles</span></div>
  <div><strong>100%</strong><span>Conflict-safe allocation</span></div>
  <div><strong>REST</strong><span>PHP + MySQL API</span></div>
</section>

<section id="features" class="features-section">
  <h2>Everything your organization needs</h2>
  <div class="grid grid-3">
    <article class="card feature-card">
      <div class="feature-icon">📦</div>
      <h3>Asset Registry</h3>
      <p>Register assets with auto tags (AF-0001), search, filter, and view full allocation history.</p>
    </article>
    <article class="card feature-card">
      <div class="feature-icon">🔄</div>
      <h3>Allocation & Transfer</h3>
      <p>Prevent double-allocation conflicts and route transfer requests through approval workflows.</p>
    </article>
    <article class="card feature-card">
      <div class="feature-icon">📅</div>
      <h3>Resource Booking</h3>
      <p>Book rooms, vehicles, and shared equipment with automatic overlap validation.</p>
    </article>
    <article class="card feature-card">
      <div class="feature-icon">🔧</div>
      <h3>Maintenance</h3>
      <p>Employees raise requests; managers approve, assign technicians, and resolve issues.</p>
    </article>
    <article class="card feature-card">
      <div class="feature-icon">✅</div>
      <h3>Audit Cycles</h3>
      <p>Assign auditors, verify assets, flag missing items, and close cycles with status updates.</p>
    </article>
    <article class="card feature-card">
      <div class="feature-icon">📊</div>
      <h3>Reports & Alerts</h3>
      <p>Utilization, maintenance frequency, department allocations, booking heatmaps, and notifications.</p>
    </article>
  </div>
</section>

<section class="card cta-section">
  <h2>Ready to manage your assets?</h2>
  <p>Employees sign up directly. Admins promote roles from Organization Setup — no self-assigned admin accounts.</p>
  <div class="hero-actions" style="justify-content:center;margin-top:20px">
    <a class="btn primary large" href="signup.php">Sign Up Free</a>
    <a class="btn secondary large" href="login.php">Login</a>
  </div>
</section>

<?php render_public_footer(); ?>
