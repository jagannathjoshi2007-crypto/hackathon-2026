const Card = ({ title, children }) => {
  return (
    <div style={{ border: '1px solid #e5e7eb', borderRadius: '0.75rem', padding: '1rem', boxShadow: '0 1px 2px rgba(0,0,0,0.05)' }}>
      {title && <h3 style={{ marginTop: 0 }}>{title}</h3>}
      {children}
    </div>
  )
}

export default Card
