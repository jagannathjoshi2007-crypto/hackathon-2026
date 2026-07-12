<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Asset Allocation & Transfer', $user);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Allocate Asset</h2>
    <form id="allocateForm">
      <label>Asset</label><select name="asset_id" id="assetSelect" required></select>
      <label>Assign To Employee</label><select name="user_id" id="userSelect" required></select>
      <label>Department</label><select name="department_id" id="deptSelect"><option value="">None</option></select>
      <label>Expected Return Date</label><input type="date" name="expected_return" />
      <button type="submit" class="btn primary">Allocate</button>
    </form>
    <p style="color:#6b7280;font-size:14px">Conflict rule: cannot allocate an asset already held by someone else — use Transfer Request instead.</p>
  </section>
  <section class="card">
    <h2>Return Asset</h2>
    <form id="returnForm">
      <label>Active Allocation</label><select name="allocation_id" id="allocationSelect" required></select>
      <label>Condition Check-in Notes</label><textarea name="condition_notes" placeholder="Condition on return"></textarea>
      <button type="submit" class="btn secondary">Mark Returned</button>
    </form>
  </section>
</div>

<section class="card" style="margin-top:16px">
  <h2>Transfer Requests</h2>
  <form id="transferForm" class="grid grid-2">
    <div><label>Asset (currently allocated)</label><select name="asset_id" id="transferAssetSelect"></select></div>
    <div><label>Transfer To</label><select name="to_user_id" id="transferUserSelect"></select></div>
    <div><label>Notes</label><input name="notes" /></div>
    <div><button type="submit" class="btn secondary">Request Transfer</button></div>
  </form>
  <div id="transferTable" style="margin-top:16px"></div>
</section>

<section class="card" style="margin-top:16px">
  <h2>Current Allocations</h2>
  <div id="allocationTable"></div>
</section>

<script>
async function loadOptions() {
  const [assets, employees, depts, allocations] = await Promise.all([
    api('assets'), api('employees'), api('departments'), api('allocations')
  ]);
  const assetOpts = (assets.assets||[]).map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)} (${a.status})</option>`).join('');
  document.getElementById('assetSelect').innerHTML = assetOpts;
  document.getElementById('transferAssetSelect').innerHTML = (assets.assets||[]).filter(a => a.status === 'allocated').map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)}</option>`).join('');
  const userOpts = (employees.employees||[]).filter(e => e.status === 'active').map(e => `<option value="${e.id}">${escapeHtml(e.name)}</option>`).join('');
  document.getElementById('userSelect').innerHTML = userOpts;
  document.getElementById('transferUserSelect').innerHTML = userOpts;
  document.getElementById('deptSelect').innerHTML = '<option value="">None</option>' + (depts.departments||[]).map(d => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('');
  const active = (allocations.allocations||[]).filter(a => ['active','overdue'].includes(a.status));
  document.getElementById('allocationSelect').innerHTML = active.map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} → ${escapeHtml(a.user_name)}</option>`).join('');
  document.getElementById('allocationTable').innerHTML = renderAllocTable(allocations.allocations||[]);
}

function renderAllocTable(rows) {
  return `<table><thead><tr><th>Asset</th><th>Holder</th><th>Department</th><th>Expected Return</th><th>Status</th></tr></thead><tbody>
    ${rows.map(a => `<tr><td>${escapeHtml(a.asset_tag)}</td><td>${escapeHtml(a.user_name)}</td><td>${escapeHtml(a.department_name||'—')}</td><td>${escapeHtml(a.expected_return||'—')}</td><td>${statusBadge(a.status)}</td></tr>`).join('')}
  </tbody></table>`;
}

async function loadTransfers() {
  const data = await api('transfers');
  document.getElementById('transferTable').innerHTML = `<table><thead><tr><th>Asset</th><th>From</th><th>To</th><th>Status</th><th>Action</th></tr></thead><tbody>
    ${(data.transfers||[]).map(t => `<tr>
      <td>${escapeHtml(t.asset_tag)}</td><td>${escapeHtml(t.from_name)}</td><td>${escapeHtml(t.to_name)}</td><td>${statusBadge(t.status)}</td>
      <td>${t.status==='requested' ? `<button class="btn secondary approve" data-id="${t.id}">Approve</button> <button class="btn danger reject" data-id="${t.id}">Reject</button>` : '—'}</td>
    </tr>`).join('')}
  </tbody></table>`;
  document.querySelectorAll('.approve').forEach(btn => btn.addEventListener('click', () => updateTransfer(btn.dataset.id, 'approve')));
  document.querySelectorAll('.reject').forEach(btn => btn.addEventListener('click', () => updateTransfer(btn.dataset.id, 'reject')));
}

async function updateTransfer(id, action) {
  try {
    await api('transfers/approve', { method: 'POST', body: JSON.stringify({ id: Number(id), action }) });
    showToast('Transfer updated'); loadTransfers(); loadOptions();
  } catch (err) { showToast(err.message, 'error'); }
}

document.getElementById('allocateForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('allocations', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    showToast('Asset allocated'); loadOptions(); loadTransfers();
  } catch (err) {
    if (err.message.includes('held by')) showToast(err.message + ' — use Transfer Request.', 'error');
    else showToast(err.message, 'error');
  }
});

document.getElementById('returnForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('allocations/return', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    showToast('Asset returned'); loadOptions();
  } catch (err) { showToast(err.message, 'error'); }
});

document.getElementById('transferForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('transfers', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    showToast('Transfer requested'); loadTransfers();
  } catch (err) { showToast(err.message, 'error'); }
});

loadOptions(); loadTransfers();
</script>
<?php render_admin_footer(); ?>

