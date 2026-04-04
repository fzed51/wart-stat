import './Alert.css';

type AlertVariant = 'success' | 'error' | 'warning' | 'info';

interface AlertProps {
  variant?: AlertVariant;
  children: React.ReactNode;
  className?: string;
}

export function Alert({ variant = 'info', children, className }: AlertProps) {
  return (
    <div
      className={`alert alert--${variant}${className ? ` ${className}` : ''}`}
      role="alert"
    >
      {children}
    </div>
  );
}
