document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('navToggle');
  const links = document.getElementById('navLinks');
  toggle?.addEventListener('click', () => links?.classList.toggle('open'));

  const logout = async () => {
    try {
      await api('auth/logout', { method: 'POST', body: '{}' });
    } finally {
      window.location.href = window.ASSETFLOW_API ? '../login.php' : 'login.php';
    }
  };

  document.getElementById('logoutBtn')?.addEventListener('click', logout);
  document.querySelector('.sidebar-logout')?.addEventListener('click', logout);
});
