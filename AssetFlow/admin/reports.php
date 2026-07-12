<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Reports & Analytics', $user);
?>
<section class="grid grid-2">
  <div class="card">
    <h2>Asset Utilization (Top Used)</h2>
    <div id="utilization"></div>
  </div>
  <div class="card">
    <h2>Maintenance Frequency by Category</h2>
    <div id="maintenanceChart"></div>
  </div>
  <div class="card">
    <h2>Department-wise Allocation</h2>
    <div id="deptAlloc"></div>
  </div>
  <div class="card">
    <h2>Resource Booking Heatmap</h2>
    <div id="heatmap" class="heatmap"></div>
  </div>
</section>

<section class="card" style="margin-top:16px">
  <button class="btn secondary" id="exportBtn">Export Report Summary (JSON)</button>
</section>

<script>
let reportData = {};

async function loadReports() {
  reportData = await api('reports');
  document.getElementById('utilization').innerHTML = tableFrom(reportData.utilization, ['asset_tag','name','allocation_count']);
  document.getElementById('maintenanceChart').innerHTML = tableFrom(reportData.maintenance_by_category, ['category','request_count']);
  document.getElementById('deptAlloc').innerHTML = tableFrom(reportData.department_allocations, ['department','allocations']);
  document.getElementById('heatmap').innerHTML = (reportData.booking_heatmap||[]).map(h =>
    `<div class="heat-cell"><strong>${h.hour_slot}:00</strong><span>${h.bookings} bookings</span></div>`
  ).join('') || '<p class="empty">No booking data yet</p>';
}

function tableFrom(rows, cols) {
  if (!rows?.length) return '<p class="empty">No data</p>';
  return `<table><thead><tr>${cols.map(c => `<th>${c.replaceAll('_',' ')}</th>`).join('')}</tr></thead><tbody>
    ${rows.map(r => `<tr>${cols.map(c => `<td>${escapeHtml(String(r[c] ?? 0))}</td>`).join('')}</tr>`).join('')}
  </tbody></table>`;
}

document.getElementById('exportBtn').addEventListener('click', () => {
  const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'assetflow-report.json';
  a.click();
});

loadReports().catch(err => showToast(err.message, 'error'));
</script>
<?php render_admin_footer(); ?>

