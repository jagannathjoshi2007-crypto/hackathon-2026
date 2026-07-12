document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('navToggle');
  const links = document.getElementById('navLinks');
  toggle?.addEventListener('click', () => links?.classList.toggle('open'));

  document.getElementById('publicLogout')?.addEventListener('click', async () => {
    try {
      await api('auth/logout', { method: 'POST', body: '{}' });
    } finally {
      window.location.href = 'index.php';
    }
  });
});
