import './Input.css';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  prefix?: string;
}

export function Input({ label, error, prefix, id, className, ...props }: InputProps) {
  const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

  return (
    <div
      className={`input-field${error ? ' input-field--error' : ''}${className ? ` ${className}` : ''}`}
    >
      {label && <label htmlFor={inputId}>{label}</label>}
      <div className="input-field__wrapper">
        {prefix && (
          <span className="input-field__prefix" aria-hidden="true">
            {prefix}
          </span>
        )}
        <input
          id={inputId}
          className={prefix ? 'input--has-prefix' : undefined}
          {...props}
        />
      </div>
      {error && (
        <span className="input-field__error" role="alert">
          {error}
        </span>
      )}
    </div>
  );
}
