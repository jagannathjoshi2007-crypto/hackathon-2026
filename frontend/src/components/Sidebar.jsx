const Sidebar = () => {
  const items = ['Home', 'Orders', 'Customers', 'Inventory']

  return (
    <aside style={{ width: '220px', padding: '1rem', borderRight: '1px solid #e5e7eb', minHeight: '100vh' }}>
      <h3 style={{ marginBottom: '1rem' }}>Menu</h3>
      <ul style={{ listStyle: 'none', padding: 0, display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
        {items.map((item) => (
          <li key={item}>{item}</li>
        ))}
      </ul>
    </aside>
  )
}

export default Sidebar
