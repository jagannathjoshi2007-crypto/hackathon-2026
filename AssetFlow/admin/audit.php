<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_roles(['admin', 'asset_manager']);
render_admin_header('Asset Audit', $user);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Create Audit Cycle</h2>
    <form id="auditForm">
      <label>Cycle Name</label><input name="name" required />
      <label>Scope Department</label><select name="scope_department_id" id="deptSelect"><option value="">All</option></select>
      <label>Scope Location</label><input name="scope_location" />
      <label>Start Date</label><input type="date" name="start_date" required />
      <label>End Date</label><input type="date" name="end_date" required />
      <label>Assign Auditors (hold Ctrl for multiple)</label><select name="auditor_ids" id="auditorSelect" multiple size="4"></select>
      <button type="submit" class="btn primary">Create Audit Cycle</button>
    </form>
  </section>
  <section class="card">
    <h2>Audit Cycles</h2>
    <div id="auditList"></div>
  </section>
</div>

<section class="card" style="margin-top:16px">
  <h2>Verify Assets (Auditor)</h2>
  <form id="verifyForm" class="grid grid-2">
    <div><label>Audit Cycle ID</label><input name="audit_cycle_id" type="number" required /></div>
    <div><label>Asset ID</label><input name="asset_id" type="number" required /></div>
    <div><label>Status</label><select name="status"><option>verified</option><option>missing</option><option>damaged</option></select></div>
    <div><label>Notes</label><input name="notes" /></div>
    <div><button type="submit" class="btn secondary">Update Audit Item</button></div>
  </form>
</section>

<script>
async function loadMeta() {
  const [depts, emps, audits] = await Promise.all([api('departments'), api('employees'), api('audits')]);
  document.getElementById('deptSelect').innerHTML = '<option value="">All</option>' + (depts.departments||[]).map(d => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('');
  document.getElementById('auditorSelect').innerHTML = (emps.employees||[]).filter(e => e.status==='active').map(e => `<option value="${e.id}">${escapeHtml(e.name)}</option>`).join('');
  document.getElementById('auditList').innerHTML = (audits.audits||[]).length
    ? `<table><thead><tr><th>ID</th><th>Name</th><th>Period</th><th>Status</th><th></th></tr></thead><tbody>
      ${audits.audits.map(a => `<tr><td>${a.id}</td><td>${escapeHtml(a.name)}</td><td>${a.start_date} → ${a.end_date}</td><td>${statusBadge(a.status)}</td>
      <td>${a.status !== 'closed' ? `<button class="btn danger close-audit" data-id="${a.id}">Close Cycle</button>` : 'Closed'}</td></tr>`).join('')}
    </tbody></table>`
    : '<p class="empty">No audit cycles yet</p>';
  document.querySelectorAll('.close-audit').forEach(btn => btn.addEventListener('click', async () => {
    if (!confirm('Close audit cycle and update missing assets to Lost?')) return;
    try {
      await api('audits/close', { method: 'POST', body: JSON.stringify({ audit_cycle_id: Number(btn.dataset.id) }) });
      showToast('Audit cycle closed'); loadMeta();
    } catch (err) { showToast(err.message, 'error'); }
  }));
}

document.getElementById('auditForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const auditorIds = [...document.getElementById('auditorSelect').selectedOptions].map(o => Number(o.value));
  const payload = Object.fromEntries(fd.entries());
  payload.auditor_ids = auditorIds;
  try {
    await api('audits', { method: 'POST', body: JSON.stringify(payload) });
    showToast('Audit cycle created'); e.target.reset(); loadMeta();
  } catch (err) { showToast(err.message, 'error'); }
});

document.getElementById('verifyForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('audits/items', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    showToast('Audit item updated');
  } catch (err) { showToast(err.message, 'error'); }
});

loadMeta();
</script>
<?php render_admin_footer(); ?>

