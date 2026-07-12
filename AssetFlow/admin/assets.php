<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Asset Registration & Directory', $user);
$canManage = in_array($user['role'], ['admin', 'asset_manager'], true);
?>
<div class="grid grid-2">
  <?php if ($canManage): ?>
  <section class="card">
    <h2>Register Asset</h2>
    <form id="assetForm">
      <label>Name</label><input name="name" required />
      <label>Category</label><select name="category_id" id="categorySelect" required></select>
      <label>Serial Number</label><input name="serial_number" />
      <label>Acquisition Date</label><input type="date" name="acquisition_date" />
      <label>Acquisition Cost</label><input type="number" step="0.01" name="acquisition_cost" />
      <label>Condition</label><input name="condition_note" value="Good" />
      <label>Location</label><input name="location" />
      <label><input type="checkbox" name="is_bookable" value="1" /> Shared / Bookable resource</label>
      <button type="submit" class="btn primary">Register Asset</button>
    </form>
  </section>
  <?php endif; ?>
  <section class="card">
    <h2>Search & Filter</h2>
    <div class="filters">
      <input id="searchQ" placeholder="Tag, serial, name..." />
      <select id="filterStatus"><option value="">All statuses</option>
        <option>available</option><option>allocated</option><option>reserved</option>
        <option>under_maintenance</option><option>lost</option><option>retired</option><option>disposed</option>
      </select>
      <select id="filterCategory"><option value="">All categories</option></select>
      <button class="btn secondary" id="applyFilters">Apply</button>
    </div>
    <div id="assetTable"></div>
  </section>
</div>

<div class="card" style="margin-top:16px" id="historyPanel" hidden>
  <h2>Asset History</h2>
  <div id="historyContent"></div>
</div>

<script>
const canManage = <?= $canManage ? 'true' : 'false' ?>;

async function loadCategories() {
  const data = await api('categories');
  const opts = (data.categories || []).map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
  document.getElementById('filterCategory').innerHTML = '<option value="">All categories</option>' + opts;
  if (canManage) document.getElementById('categorySelect').innerHTML = opts;
}

async function loadAssets() {
  const params = new URLSearchParams();
  const q = document.getElementById('searchQ').value.trim();
  const status = document.getElementById('filterStatus').value;
  const category = document.getElementById('filterCategory').value;
  if (q) params.set('q', q);
  if (status) params.set('status', status);
  if (category) params.set('category_id', category);
  const data = await api('assets&' + params.toString());
  document.getElementById('assetTable').innerHTML = `<table><thead><tr><th>Tag</th><th>Name</th><th>Category</th><th>Status</th><th>Location</th><th></th></tr></thead><tbody>
    ${(data.assets||[]).map(a => `<tr>
      <td><strong>${escapeHtml(a.asset_tag)}</strong></td>
      <td>${escapeHtml(a.name)}</td><td>${escapeHtml(a.category_name)}</td>
      <td>${statusBadge(a.status)}</td><td>${escapeHtml(a.location || '—')}</td>
      <td><button class="btn secondary view-history" data-id="${a.id}">History</button></td>
    </tr>`).join('')}
  </tbody></table>`;
  document.querySelectorAll('.view-history').forEach(btn => btn.addEventListener('click', () => showHistory(btn.dataset.id)));
}

async function showHistory(assetId) {
  const data = await api('assets/history&asset_id=' + assetId);
  document.getElementById('historyPanel').hidden = false;
  document.getElementById('historyContent').innerHTML = `
    <h3>Allocation History</h3>${renderTable(data.allocations, ['user_name','allocated_at','expected_return','status'])}
    <h3>Maintenance History</h3>${renderTable(data.maintenance, ['description','priority','status','created_at'])}
  `;
}

function renderTable(rows, cols) {
  if (!rows?.length) return '<p class="empty">No records</p>';
  return `<table><thead><tr>${cols.map(c => `<th>${c.replaceAll('_',' ')}</th>`).join('')}</tr></thead><tbody>
    ${rows.map(r => `<tr>${cols.map(c => `<td>${escapeHtml(String(r[c] ?? '—'))}</td>`).join('')}</tr>`).join('')}
  </tbody></table>`;
}

if (canManage) {
  document.getElementById('assetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    payload.is_bookable = fd.get('is_bookable') ? 1 : 0;
    try {
      const res = await api('assets', { method: 'POST', body: JSON.stringify(payload) });
      showToast(`Asset registered: ${res.asset_tag}`);
      e.target.reset(); loadAssets();
    } catch (err) { showToast(err.message, 'error'); }
  });
}

document.getElementById('applyFilters').addEventListener('click', loadAssets);
loadCategories().then(loadAssets);
</script>
<?php render_admin_footer(); ?>

