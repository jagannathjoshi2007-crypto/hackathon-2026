<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-user.php';
$user = require_user_panel();
render_user_header('Maintenance Request', $user);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Raise Maintenance Request</h2>
    <form id="maintForm">
      <label>Asset</label><select name="asset_id" id="assetSelect" required></select>
      <label>Issue Description</label><textarea name="description" required placeholder="Describe the problem..."></textarea>
      <label>Priority</label>
      <select name="priority"><option>low</option><option selected>medium</option><option>high</option><option>critical</option></select>
      <button type="submit" class="btn primary">Submit Request</button>
    </form>
  </section>
  <section class="card">
    <h2>My Maintenance Requests</h2>
    <div id="maintTable"></div>
  </section>
</div>

<script>
const myId = <?= (int) $user['id'] ?>;

async function loadAssets() {
  const [assets, allocations] = await Promise.all([api('assets'), api('allocations')]);
  const myAssetIds = new Set((allocations.allocations||[]).filter(a => Number(a.user_id) === myId && a.status !== 'returned').map(a => String(a.asset_id)));
  const list = (assets.assets||[]).filter(a => myAssetIds.has(String(a.id)) || a.status === 'allocated');
  document.getElementById('assetSelect').innerHTML = list.map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)}</option>`).join('') || '<option value="">No assets available</option>';
}

async function loadMaintenance() {
  const data = await api('maintenance');
  const mine = (data.requests||[]).filter(m => Number(m.requested_by) === myId);
  document.getElementById('maintTable').innerHTML = mine.length
    ? `<table><thead><tr><th>Asset</th><th>Priority</th><th>Status</th><th>Submitted</th></tr></thead><tbody>
      ${mine.map(m => `<tr><td>${escapeHtml(m.asset_tag)}</td><td>${statusBadge(m.priority)}</td><td>${statusBadge(m.status)}</td><td>${formatDate(m.created_at)}</td></tr>`).join('')}
      </tbody></table>`
    : '<p class="empty">No maintenance requests yet.</p>';
}

document.getElementById('maintForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('maintenance', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    showToast('Request submitted'); e.target.reset(); loadMaintenance();
  } catch (err) { showToast(err.message, 'error'); }
});

loadAssets(); loadMaintenance();
</script>
<?php render_user_footer(); ?>
