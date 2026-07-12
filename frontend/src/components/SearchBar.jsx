const SearchBar = ({ placeholder = 'Search...', ...props }) => {
  return (
    <input
      type="text"
      placeholder={placeholder}
      style={{
        padding: '0.7rem 0.8rem',
        borderRadius: '0.5rem',
        border: '1px solid #d1d5db',
        width: '100%',
      }}
      {...props}
    />
  )
}

export default SearchBar
