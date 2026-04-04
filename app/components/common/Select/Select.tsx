import './Select.css';

interface SelectOption {
  value: string;
  label: string;
}

interface SelectProps {
  label?: string;
  options: SelectOption[];
  value: string;
  onChange: (value: string) => void;
  error?: string;
  id?: string;
  placeholder?: string;
  className?: string;
}

export function Select({
  label,
  options,
  value,
  onChange,
  error,
  id,
  placeholder,
  className,
}: SelectProps) {
  const selectId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

  return (
    <div
      className={`select-field${error ? ' select-field--error' : ''}${className ? ` ${className}` : ''}`}
    >
      {label && <label htmlFor={selectId}>{label}</label>}
      <select id={selectId} value={value} onChange={(e) => onChange(e.target.value)}>
        {placeholder && (
          <option value="" disabled>
            {placeholder}
          </option>
        )}
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
      {error && (
        <span className="select-field__error" role="alert">
          {error}
        </span>
      )}
    </div>
  );
}
