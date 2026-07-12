const Button = ({ children, variant = 'primary', ...props }) => {
  const baseStyle = {
    padding: '0.65rem 1rem',
    border: 'none',
    borderRadius: '0.5rem',
    cursor: 'pointer',
    fontWeight: 600,
  }

  const variants = {
    primary: { backgroundColor: '#2563eb', color: '#fff' },
    secondary: { backgroundColor: '#e5e7eb', color: '#111827' },
    danger: { backgroundColor: '#dc2626', color: '#fff' },
  }

  return (
    <button style={{ ...baseStyle, ...variants[variant] }} {...props}>
      {children}
    </button>
  )
}

export default Button
