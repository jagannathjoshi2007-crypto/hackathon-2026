<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Notifications & Activity Logs', $user);
$canViewLogs = in_array($user['role'], ['admin', 'asset_manager'], true);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Notifications</h2>
    <div id="notifList"></div>
  </section>
  <?php if ($canViewLogs): ?>
  <section class="card">
    <h2>Activity Logs</h2>
    <div id="activityList"></div>
  </section>
  <?php endif; ?>
</div>

<script>
async function loadNotifications() {
  const data = await api('notifications');
  document.getElementById('notifList').innerHTML = (data.notifications||[]).length
    ? (data.notifications||[]).map(n => `<article style="padding:12px 0;border-bottom:1px solid #e5e7eb;opacity:${n.is_read==1?0.7:1}">
      <strong>${escapeHtml(n.title)}</strong><br>
      <span>${escapeHtml(n.message)}</span><br>
      <small>${formatDate(n.created_at)} · ${escapeHtml(n.type)}</small>
      ${n.is_read==0 ? `<button class="btn secondary mark-read" data-id="${n.id}" style="margin-top:8px">Mark read</button>` : ''}
    </article>`).join('')
    : '<p class="empty">No notifications</p>';
  document.querySelectorAll('.mark-read').forEach(btn => btn.addEventListener('click', async () => {
    await api('notifications/read', { method: 'POST', body: JSON.stringify({ id: Number(btn.dataset.id) }) });
    loadNotifications();
  }));
}

<?php if ($canViewLogs): ?>
async function loadActivity() {
  const data = await api('activity');
  document.getElementById('activityList').innerHTML = `<table><thead><tr><th>User</th><th>Action</th><th>Details</th><th>When</th></tr></thead><tbody>
    ${(data.logs||[]).map(l => `<tr><td>${escapeHtml(l.user_name||'System')}</td><td>${escapeHtml(l.action)}</td><td>${escapeHtml(l.details||l.entity_type||'—')}</td><td>${formatDate(l.created_at)}</td></tr>`).join('')}
  </tbody></table>`;
}
loadActivity();
<?php endif; ?>

loadNotifications();
</script>
<?php render_admin_footer(); ?>

