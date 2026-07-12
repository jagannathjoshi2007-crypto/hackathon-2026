<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-user.php';
$user = require_user_panel();
render_user_header('Notifications', $user);
?>
<section class="card">
  <h2>My Notifications</h2>
  <div id="notifList"></div>
</section>

<script>
async function loadNotifications() {
  const data = await api('notifications');
  document.getElementById('notifList').innerHTML = (data.notifications||[]).length
    ? data.notifications.map(n => `<article style="padding:12px 0;border-bottom:1px solid #e5e7eb;opacity:${n.is_read==1?0.7:1}">
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
loadNotifications();
</script>
<?php render_user_footer(); ?>
