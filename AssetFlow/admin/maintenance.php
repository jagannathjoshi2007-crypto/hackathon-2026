<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Maintenance Management', $user);
$canApprove = in_array($user['role'], ['admin', 'asset_manager'], true);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Raise Maintenance Request</h2>
    <form id="maintForm">
      <label>Asset</label><select name="asset_id" id="assetSelect" required></select>
      <label>Issue Description</label><textarea name="description" required></textarea>
      <label>Priority</label>
      <select name="priority"><option>low</option><option selected>medium</option><option>high</option><option>critical</option></select>
      <button type="submit" class="btn primary">Submit Request</button>
    </form>
    <p style="color:#6b7280;font-size:14px">Workflow: Pending → Approved/Rejected → Technician Assigned → In Progress → Resolved. Asset moves to Under Maintenance on approval.</p>
  </section>
  <section class="card">
    <h2>Maintenance Queue</h2>
    <div id="maintTable"></div>
  </section>
</div>

<script>
const canApprove = <?= $canApprove ? 'true' : 'false' ?>;

async function loadAssets() {
  const data = await api('assets');
  document.getElementById('assetSelect').innerHTML = (data.assets||[]).map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)}</option>`).join('');
}

async function loadMaintenance() {
  const data = await api('maintenance');
  document.getElementById('maintTable').innerHTML = `<table><thead><tr><th>Asset</th><th>Requester</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    ${(data.requests||[]).map(m => `<tr>
      <td>${escapeHtml(m.asset_tag)}</td><td>${escapeHtml(m.requester_name)}</td><td>${statusBadge(m.priority)}</td><td>${statusBadge(m.status)}</td>
      <td>${renderActions(m)}</td>
    </tr>`).join('')}
  </tbody></table>`;
  bindActions();
}

function renderActions(m) {
  if (canApprove && m.status === 'pending') {
    return `<button class="btn secondary act" data-id="${m.id}" data-status="approved">Approve</button>
            <button class="btn danger act" data-id="${m.id}" data-status="rejected">Reject</button>`;
  }
  if (m.status === 'approved') {
    return `<button class="btn secondary act" data-id="${m.id}" data-status="in_progress" data-tech="1">Assign & Start</button>`;
  }
  if (m.status === 'in_progress') {
    return `<button class="btn secondary act" data-id="${m.id}" data-status="resolved">Mark Resolved</button>`;
  }
  return '—';
}

function bindActions() {
  document.querySelectorAll('.act').forEach(btn => btn.addEventListener('click', async () => {
    const payload = { id: Number(btn.dataset.id), status: btn.dataset.status };
    if (btn.dataset.tech) payload.technician = prompt('Technician name:') || 'Assigned Tech';
    try {
      await api('maintenance/update', { method: 'POST', body: JSON.stringify(payload) });
      showToast('Maintenance updated'); loadMaintenance();
    } catch (err) { showToast(err.message, 'error'); }
  }));
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
<?php render_admin_footer(); ?>

