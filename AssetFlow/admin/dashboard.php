<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Dashboard', $user);
?>
<section class="grid grid-4" id="kpiCards"></section>

<section class="grid grid-2" style="margin-top:16px">
  <div class="card overdue-card">
    <h2>Overdue Returns</h2>
    <p class="section-note danger-text">Past expected return date</p>
    <div id="overdueList"></div>
  </div>
  <div class="card upcoming-card">
    <h2>Upcoming Returns</h2>
    <p class="section-note">Due on or after today</p>
    <div id="upcomingList"></div>
  </div>
</section>

<section class="card" style="margin-top:16px">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <?php if (in_array($user['role'], ['admin', 'asset_manager'], true)): ?>
    <a class="btn secondary" href="assets.php">Register Asset</a>
    <?php endif; ?>
    <a class="btn secondary" href="booking.php">Book Resource</a>
    <a class="btn secondary" href="maintenance.php">Raise Maintenance Request</a>
    <a class="btn secondary" href="allocation.php">Allocate Asset</a>
  </div>
</section>

<script>
function renderReturnTable(rows, emptyMsg) {
  if (!rows?.length) return `<p class="empty">${emptyMsg}</p>`;
  return `<table><thead><tr><th>Asset</th><th>Holder</th><th>Due Date</th></tr></thead><tbody>
    ${rows.map(o => `<tr><td>${escapeHtml(o.asset_tag)} · ${escapeHtml(o.asset_name)}</td><td>${escapeHtml(o.holder)}</td><td>${escapeHtml(o.expected_return)}</td></tr>`).join('')}
  </tbody></table>`;
}

async function loadDashboard() {
  const data = await api('dashboard');
  const s = data.stats;
  const cards = [
    ['Assets Available', s.assets_available],
    ['Assets Allocated', s.assets_allocated],
    ['Maintenance Today', s.maintenance_today],
    ['Active Bookings', s.active_bookings],
    ['Pending Transfers', s.pending_transfers],
    ['Upcoming Returns', s.upcoming_returns],
    ['Overdue Returns', s.overdue_returns, true],
  ];
  document.getElementById('kpiCards').innerHTML = cards.map(([label, value, danger]) => `
    <article class="card kpi ${danger ? 'danger' : ''}">
      <span>${label}</span>
      <strong>${value}</strong>
    </article>
  `).join('');

  document.getElementById('overdueList').innerHTML = renderReturnTable(data.overdue_returns, 'No overdue returns');
  document.getElementById('upcomingList').innerHTML = renderReturnTable(data.upcoming_returns, 'No upcoming returns scheduled');
}
loadDashboard().catch(err => showToast(err.message, 'error'));
</script>
<?php render_admin_footer(); ?>
