const API_BASE = window.ASSETFLOW_API || 'api/index.php?route=';

async function api(route, options = {}) {
  const res = await fetch(API_BASE + route, {
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    credentials: 'same-origin',
    ...options,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    throw new Error(data.error || 'Request failed');
  }
  return data;
}

function showToast(message, type = 'info') {
  let toast = document.querySelector('.toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.dataset.type = type;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3200);
}

function formatDate(value) {
  if (!value) return '—';
  return new Date(value).toLocaleString();
}

function statusBadge(status) {
  return `<span class="badge status-${status}">${String(status).replaceAll('_', ' ')}</span>`;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}
