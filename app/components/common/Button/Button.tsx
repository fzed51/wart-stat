import './Button.css';

type ButtonVariant = 'default' | 'primary' | 'danger' | 'ghost';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
}

export function Button({ variant = 'default', className, children, ...props }: ButtonProps) {
  const variantClass = variant !== 'default' ? ` btn--${variant}` : '';
  return (
    <button
      className={`btn${variantClass}${className ? ` ${className}` : ''}`}
      {...props}
    >
      {children}
    </button>
  );
}
