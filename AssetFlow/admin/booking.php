<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout-admin.php';
$user = require_admin_panel();
render_admin_header('Resource Booking', $user);
?>
<div class="grid grid-2">
  <section class="card">
    <h2>Book Shared Resource</h2>
    <form id="bookingForm">
      <label>Bookable Resource</label><select name="asset_id" id="bookableSelect" required></select>
      <label>Start Time</label><input type="datetime-local" name="start_time" required />
      <label>End Time</label><input type="datetime-local" name="end_time" required />
      <button type="submit" class="btn primary">Create Booking</button>
    </form>
    <p style="color:#6b7280;font-size:14px">Overlap validation: overlapping time slots for the same resource are rejected automatically.</p>
  </section>
  <section class="card">
    <h2>Resource Calendar</h2>
    <label>Select resource to view schedule</label>
    <select id="calendarAsset"></select>
    <div id="calendarView" style="margin-top:16px"></div>
  </section>
</div>

<section class="card" style="margin-top:16px">
  <h2>All Bookings</h2>
  <div id="bookingTable"></div>
</section>

<script>
async function loadBookableAssets() {
  const data = await api('assets');
  const bookable = (data.assets||[]).filter(a => Number(a.is_bookable) === 1);
  const opts = bookable.map(a => `<option value="${a.id}">${escapeHtml(a.asset_tag)} · ${escapeHtml(a.name)}</option>`).join('');
  document.getElementById('bookableSelect').innerHTML = opts;
  document.getElementById('calendarAsset').innerHTML = opts;
}

async function loadBookings(assetId = null) {
  const route = assetId ? `bookings&asset_id=${assetId}` : 'bookings';
  const data = await api(route);
  document.getElementById('bookingTable').innerHTML = `<table><thead><tr><th>Resource</th><th>User</th><th>Start</th><th>End</th><th>Status</th><th></th></tr></thead><tbody>
    ${(data.bookings||[]).map(b => `<tr>
      <td>${escapeHtml(b.asset_tag)} · ${escapeHtml(b.asset_name)}</td>
      <td>${escapeHtml(b.user_name)}</td><td>${formatDate(b.start_time)}</td><td>${formatDate(b.end_time)}</td>
      <td>${statusBadge(b.status)}</td>
      <td>${['upcoming','ongoing'].includes(b.status) ? `<button class="btn danger cancel-booking" data-id="${b.id}">Cancel</button>` : ''}</td>
    </tr>`).join('')}
  </tbody></table>`;
  document.querySelectorAll('.cancel-booking').forEach(btn => btn.addEventListener('click', async () => {
    try {
      await api('bookings/cancel', { method: 'POST', body: JSON.stringify({ id: Number(btn.dataset.id) }) });
      showToast('Booking cancelled'); refreshAll();
    } catch (err) { showToast(err.message, 'error'); }
  }));
}

async function loadCalendar() {
  const assetId = document.getElementById('calendarAsset').value;
  if (!assetId) return;
  const data = await api('bookings&asset_id=' + assetId);
  document.getElementById('calendarView').innerHTML = (data.bookings||[]).length
    ? (data.bookings||[]).map(b => `<div class="heat-cell"><strong>${formatDate(b.start_time)}</strong><span>→ ${formatDate(b.end_time)}</span><br>${statusBadge(b.status)}</div>`).join('')
    : '<p class="empty">No bookings for this resource</p>';
}

document.getElementById('bookingForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const start = fd.get('start_time').replace('T', ' ') + ':00';
  const end = fd.get('end_time').replace('T', ' ') + ':00';
  try {
    await api('bookings', { method: 'POST', body: JSON.stringify({ asset_id: Number(fd.get('asset_id')), start_time: start, end_time: end }) });
    showToast('Booking confirmed'); refreshAll();
  } catch (err) { showToast(err.message, 'error'); }
});

document.getElementById('calendarAsset').addEventListener('change', loadCalendar);

function refreshAll() { loadBookings(); loadCalendar(); }

loadBookableAssets().then(() => { loadBookings(); loadCalendar(); });
</script>
<?php render_admin_footer(); ?>

