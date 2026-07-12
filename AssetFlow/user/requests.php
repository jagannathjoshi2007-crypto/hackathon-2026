<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-user.php';
$user = require_user_panel();
$canApprove = $user['role'] === 'department_head';
render_user_header('Return & Transfer Requests', $user);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Return My Asset</h2>
    <form id="returnForm">
      <label>Active Allocation</label><select name="allocation_id" id="allocationSelect" required></select>
      <label>Condition Check-in Notes</label><textarea name="condition_notes" placeholder="Condition on return"></textarea>
      <button type="submit" class="btn secondary">Mark Returned</button>
    </form>
  </section>
  <section class="card">
    <h2>Request Transfer</h2>
    <form id="transferForm">
      <label>Allocated Asset</label><select name="asset_id" id="transferAssetSelect"></select>
      <label>Transfer To</label><select name="to_user_id" id="transferUserSelect"></select>
      <label>Notes</label><input name="notes" />
      <button type="submit" class="btn secondary">Request Transfer</button>
    </form>
  </section>
</div>

<?php if ($canApprove): ?>
<section class="card" style="margin-top:16px">
  <h2>Pending Transfer Approvals (Department Head)</h2>
  <div id="approvalTable"></div>
</section>
<?php endif; ?>

<section class="card" style="margin-top:16px">
  <h2>My Transfer Requests</h2>
  <div id="transferTable"></div>
</section>

<script>
const myId = <?= (int) $user['id'] ?>;
const canApprove = <?= $canApprove ? 'true' : 'false' ?>;

async function loadOptions() {
  const [allocations, assets, employees, transfers] = await Promise.all([
    api('allocations'), api('assets'), api('employees'), api('transfers')
  ]);
  const mine = (allocations.allocations||[]).filter(a => Number(a.user_id) === myId && ['active','overdue'].includes(a.status));
  document.getElementById('allocationSelect').innerHTML = mine.map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} → ${escapeHtml(a.asset_name)}</option>`).join('') || '<option value="">No active allocations</option>';
  document.getElementById('transferAssetSelect').innerHTML = mine.map(a => `<option value="${a.asset_id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.asset_name)}</option>`).join('') || '<option value="">No allocated assets</option>';
  document.getElementById('transferUserSelect').innerHTML = (employees.employees||[]).filter(e => Number(e.id) !== myId && e.status === 'active').map(e => `<option value="${e.id}">${escapeHtml(e.name)}</option>`).join('');

  const allTransfers = transfers.transfers || [];
  document.getElementById('transferTable').innerHTML = allTransfers.filter(t => Number(t.to_user_id) === myId || Number(t.from_user_id) === myId).length
    ? `<table><thead><tr><th>Asset</th><th>From</th><th>To</th><th>Status</th></tr></thead><tbody>
      ${allTransfers.filter(t => Number(t.to_user_id) === myId || Number(t.from_user_id) === myId).map(t => `<tr><td>${escapeHtml(t.asset_tag)}</td><td>${escapeHtml(t.from_name)}</td><td>${escapeHtml(t.to_name)}</td><td>${statusBadge(t.status)}</td></tr>`).join('')}
      </tbody></table>`
    : '<p class="empty">No transfer requests yet.</p>';

  if (canApprove) {
    const pending = allTransfers.filter(t => t.status === 'requested');
    document.getElementById('approvalTable').innerHTML = pending.length
      ? `<table><thead><tr><th>Asset</th><th>From</th><th>To</th><th>Action</th></tr></thead><tbody>
        ${pending.map(t => `<tr><td>${escapeHtml(t.asset_tag)}</td><td>${escapeHtml(t.from_name)}</td><td>${escapeHtml(t.to_name)}</td>
        <td><button class="btn secondary approve" data-id="${t.id}">Approve</button> <button class="btn danger reject" data-id="${t.id}">Reject</button></td></tr>`).join('')}
        </tbody></table>`
      : '<p class="empty">No pending approvals.</p>';
    document.querySelectorAll('.approve').forEach(btn => btn.addEventListener('click', () => updateTransfer(btn.dataset.id, 'approve')));
    document.querySelectorAll('.reject').forEach(btn => btn.addEventListener('click', () => updateTransfer(btn.dataset.id, 'reject')));
  }
}

async function updateTransfer(id, action) {
  try {
    await api('transfers/approve', { method: 'POST', body: JSON.stringify({ id: Number(id), action }) });
    showToast('Transfer updated'); loadOptions();
  } catch (err) { showToast(err.message, 'error'); }
}

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
    showToast('Transfer requested'); loadOptions();
  } catch (err) { showToast(err.message, 'error'); }
});

loadOptions();
</script>
<?php render_user_footer(); ?>
