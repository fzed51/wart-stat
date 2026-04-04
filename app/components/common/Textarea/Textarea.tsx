import './Textarea.css';

interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string;
  error?: string;
  maxLength?: number;
  showCounter?: boolean;
}

export function Textarea({
  label,
  error,
  id,
  className,
  value = '',
  maxLength,
  showCounter = false,
  ...props
}: TextareaProps) {
  const textareaId = id ?? label?.toLowerCase().replace(/\s+/g, '-');
  const charCount = typeof value === 'string' ? value.length : 0;
  const isWarning = maxLength && charCount > maxLength * 0.8;
  const isError = maxLength && charCount > maxLength;

  return (
    <div
      className={`textarea-field${error || isError ? ' textarea-field--error' : ''}${
        className ? ` ${className}` : ''
      }`}
    >
      {label && <label htmlFor={textareaId}>{label}</label>}
      <div className="textarea-field__wrapper">
        <textarea
          id={textareaId}
          maxLength={maxLength}
          value={value}
          {...props}
        />
      </div>
      {showCounter && (
        <span
          className={`textarea-field__counter${isError ? ' error' : isWarning ? ' warning' : ' valid'}`}
        >
          {charCount}
          {maxLength && ` / ${maxLength}`} caractères
        </span>
      )}
      {error && (
        <span className="textarea-field__error" role="alert">
          {error}
        </span>
      )}
    </div>
  );
}
