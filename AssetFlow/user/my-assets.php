<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-user.php';
$user = require_user_panel();
render_user_header('My Assets', $user);
?>
<section class="card">
  <h2>Assets Allocated To Me</h2>
  <div id="myAssetsTable"></div>
</section>

<section class="card" style="margin-top:16px">
  <h2>Available Shared Resources</h2>
  <div id="bookableAssets"></div>
</section>

<script>
const myId = <?= (int) $user['id'] ?>;

async function loadMyAssets() {
  const [allocations, assets] = await Promise.all([api('allocations'), api('assets')]);
  const mine = (allocations.allocations || []).filter(a => Number(a.user_id) === myId);
  document.getElementById('myAssetsTable').innerHTML = mine.length
    ? `<table><thead><tr><th>Asset</th><th>Allocated</th><th>Expected Return</th><th>Status</th></tr></thead><tbody>
      ${mine.map(a => `<tr><td>${escapeHtml(a.asset_tag)} · ${escapeHtml(a.asset_name)}</td><td>${formatDate(a.allocated_at)}</td><td>${escapeHtml(a.expected_return || '—')}</td><td>${statusBadge(a.status)}</td></tr>`).join('')}
      </tbody></table>`
    : '<p class="empty">You have no allocated assets.</p>';

  const bookable = (assets.assets || []).filter(a => Number(a.is_bookable) === 1 && a.status === 'available');
  document.getElementById('bookableAssets').innerHTML = bookable.length
    ? `<table><thead><tr><th>Resource</th><th>Location</th><th></th></tr></thead><tbody>
      ${bookable.map(a => `<tr><td>${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)}</td><td>${escapeHtml(a.location || '—')}</td><td><a class="btn secondary" href="booking.php">Book</a></td></tr>`).join('')}
      </tbody></table>`
    : '<p class="empty">No shared resources available right now.</p>';
}

loadMyAssets().catch(err => showToast(err.message, 'error'));
</script>
<?php render_user_footer(); ?>
