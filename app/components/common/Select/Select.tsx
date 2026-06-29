import { Selector } from '@fzed51/green-terminal';
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
      <Selector
        id={selectId}
        value={value}
        state={error ? 'error' : 'default'}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </Selector>
      {error && (
        <span className="select-field__error" role="alert">
          {error}
        </span>
      )}
    </div>
  );
}
