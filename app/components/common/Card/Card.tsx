import './Card.css';

interface CardProps {
  title?: string;
  children: React.ReactNode;
  className?: string;
  onClick?: () => void;
}

export function Card({ title, children, className, onClick }: CardProps) {
  return (
    <div
      className={`card${onClick ? ' card--clickable' : ''}${className ? ` ${className}` : ''}`}
      onClick={onClick}
    >
      {title && <div className="card__header">{title}</div>}
      <div className="card__body">{children}</div>
    </div>
  );
}
