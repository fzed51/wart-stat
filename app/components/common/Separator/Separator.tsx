import './Separator.css';

interface SeparatorProps {
  label?: string;
  className?: string;
}

export function Separator({ label, className }: SeparatorProps) {
  return (
    <div
      className={`separator${label ? ' separator--labeled' : ''}${className ? ` ${className}` : ''}`}
    >
      {label && <span className="separator__label">{label}</span>}
    </div>
  );
}
