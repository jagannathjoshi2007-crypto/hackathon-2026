<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_roles(['admin']);
render_admin_header('Organization Setup', $user);
?>
<div class="tabs">
  <button class="tab-btn active" data-tab="departments">Departments</button>
  <button class="tab-btn" data-tab="categories">Asset Categories</button>
  <button class="tab-btn" data-tab="employees">Employee Directory</button>
</div>

<section id="departments" class="tab-panel active card">
  <h2>Department Management</h2>
  <form id="deptForm" class="grid grid-2">
    <div><label>Name</label><input name="name" required /></div>
    <div><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    <div><label>Department Head</label><select name="head_user_id" id="deptHeadSelect"><option value="">None</option></select></div>
    <div><label>Parent Department</label><select name="parent_id" id="parentDeptSelect"><option value="">None</option></select></div>
    <div><button type="submit" class="btn primary">Create Department</button></div>
  </form>
  <div id="deptTable" style="margin-top:20px"></div>
</section>

<section id="categories" class="tab-panel card">
  <h2>Asset Category Management</h2>
  <form id="catForm" class="grid grid-2">
    <div><label>Category Name</label><input name="name" placeholder="Electronics" required /></div>
    <div><label>Warranty (months)</label><input name="warranty_months" type="number" placeholder="24" /></div>
    <div><button type="submit" class="btn primary">Create Category</button></div>
  </form>
  <div id="catTable" style="margin-top:20px"></div>
</section>

<section id="employees" class="tab-panel card">
  <h2>Employee Directory</h2>
  <p>Promote employees to Department Head or Asset Manager here — the only place roles are assigned.</p>
  <div id="empTable"></div>
</section>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => btn.addEventListener('click', () => {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(btn.dataset.tab).classList.add('active');
}));

async function loadEmployees() {
  const data = await api('employees');
  const employees = data.employees || [];
  const selects = ['deptHeadSelect'];
  selects.forEach(id => {
    const el = document.getElementById(id);
    el.innerHTML = '<option value="">None</option>' + employees.filter(e => e.status === 'active').map(e => `<option value="${e.id}">${escapeHtml(e.name)}</option>`).join('');
  });
  document.getElementById('empTable').innerHTML = `<table><thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Promote</th></tr></thead><tbody>
    ${employees.map(e => `<tr>
      <td>${escapeHtml(e.name)}</td><td>${escapeHtml(e.email)}</td><td>${escapeHtml(e.department_name || '—')}</td>
      <td>${statusBadge(e.role)}</td>
      <td>
        <select data-id="${e.id}" class="role-select">
          <option value="employee" ${e.role==='employee'?'selected':''}>Employee</option>
          <option value="department_head" ${e.role==='department_head'?'selected':''}>Department Head</option>
          <option value="asset_manager" ${e.role==='asset_manager'?'selected':''}>Asset Manager</option>
        </select>
        <button class="btn secondary save-role" data-id="${e.id}">Save</button>
      </td>
    </tr>`).join('')}
  </tbody></table>`;
  document.querySelectorAll('.save-role').forEach(btn => btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const role = document.querySelector(`.role-select[data-id="${id}"]`).value;
    try {
      await api('employees/promote', { method: 'POST', body: JSON.stringify({ id: Number(id), role }) });
      showToast('Role updated');
      loadEmployees();
    } catch (err) { showToast(err.message, 'error'); }
  }));
}

async function loadDepartments() {
  const data = await api('departments');
  const rows = data.departments || [];
  document.getElementById('parentDeptSelect').innerHTML = '<option value="">None</option>' + rows.map(d => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('');
  document.getElementById('deptTable').innerHTML = `<table><thead><tr><th>Name</th><th>Head</th><th>Parent</th><th>Status</th></tr></thead><tbody>
    ${rows.map(d => `<tr><td>${escapeHtml(d.name)}</td><td>${escapeHtml(d.head_name || '—')}</td><td>${escapeHtml(d.parent_name || '—')}</td><td>${statusBadge(d.status)}</td></tr>`).join('')}
  </tbody></table>`;
}

async function loadCategories() {
  const data = await api('categories');
  document.getElementById('catTable').innerHTML = `<table><thead><tr><th>Name</th><th>Warranty (months)</th></tr></thead><tbody>
    ${(data.categories||[]).map(c => `<tr><td>${escapeHtml(c.name)}</td><td>${c.warranty_months ?? '—'}</td></tr>`).join('')}
  </tbody></table>`;
}

document.getElementById('deptForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('departments', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    e.target.reset(); showToast('Department created'); loadDepartments();
  } catch (err) { showToast(err.message, 'error'); }
});

document.getElementById('catForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    await api('categories', { method: 'POST', body: JSON.stringify(Object.fromEntries(fd.entries())) });
    e.target.reset(); showToast('Category created'); loadCategories();
  } catch (err) { showToast(err.message, 'error'); }
});

loadEmployees(); loadDepartments(); loadCategories();
</script>
<?php render_admin_footer(); ?>

