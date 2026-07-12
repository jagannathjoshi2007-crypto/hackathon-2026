const Navbar = () => {
  return (
    <nav style={{ display: 'flex', justifyContent: 'space-between', padding: '1rem 1.5rem', borderBottom: '1px solid #e5e7eb' }}>
      <strong>Odoo Hackathon</strong>
      <div style={{ display: 'flex', gap: '1rem' }}>
        <span>Dashboard</span>
        <span>Reports</span>
        <span>Settings</span>
      </div>
    </nav>
  )
}

export default Navbar
