const Input = ({ label, ...props }) => {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.35rem' }}>
      {label && <label>{label}</label>}
      <input
        style={{
          padding: '0.7rem 0.8rem',
          borderRadius: '0.5rem',
          border: '1px solid #d1d5db',
        }}
        {...props}
      />
    </div>
  )
}

export default Input
