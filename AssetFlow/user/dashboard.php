<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-user.php';
$user = require_user_panel();
render_user_header('Dashboard', $user);
?>
<section class="grid grid-4" id="kpiCards"></section>

<section class="grid grid-2" style="margin-top:16px">
  <div class="card overdue-card">
    <h2>Overdue Returns</h2>
    <p class="section-note danger-text">Past expected return date — highlighted separately</p>
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
    <a class="btn secondary" href="booking.php">Book Resource</a>
    <a class="btn secondary" href="maintenance.php">Raise Maintenance Request</a>
    <a class="btn secondary" href="requests.php">Return / Transfer Asset</a>
  </div>
</section>

<script>
const myName = <?= json_encode($user['name']) ?>;

function renderReturnTable(rows, emptyMsg, highlightMine = false) {
  if (!rows?.length) return `<p class="empty">${emptyMsg}</p>`;
  return `<table><thead><tr><th>Asset</th><th>Holder</th><th>Due Date</th></tr></thead><tbody>
    ${rows.map(o => `<tr class="${highlightMine && o.holder === myName ? 'row-mine' : ''}"><td>${escapeHtml(o.asset_tag)} · ${escapeHtml(o.asset_name)}</td><td>${escapeHtml(o.holder)}</td><td>${escapeHtml(o.expected_return)}</td></tr>`).join('')}
  </tbody></table>`;
}

async function loadDashboard() {
  const data = await api('dashboard');
  const s = data.stats;

  document.getElementById('kpiCards').innerHTML = [
    ['Assets Available', s.assets_available],
    ['Assets Allocated', s.assets_allocated],
    ['Maintenance Today', s.maintenance_today],
    ['Active Bookings', s.active_bookings],
    ['Pending Transfers', s.pending_transfers],
    ['Upcoming Returns', s.upcoming_returns],
    ['Overdue Returns', s.overdue_returns, true],
  ].map(([label, value, danger]) => `
    <article class="card kpi ${danger ? 'danger' : ''}">
      <span>${label}</span>
      <strong>${value}</strong>
    </article>
  `).join('');

  document.getElementById('overdueList').innerHTML = renderReturnTable(data.overdue_returns, 'No overdue returns', true);
  document.getElementById('upcomingList').innerHTML = renderReturnTable(data.upcoming_returns, 'No upcoming returns scheduled', true);
}

loadDashboard().catch(err => showToast(err.message, 'error'));
</script>
<?php render_user_footer(); ?>
