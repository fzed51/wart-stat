import { Card as GtCard } from '@fzed51/green-terminal';
import './Card.css';

interface CardProps {
  title?: string;
  children: React.ReactNode;
  className?: string;
  onClick?: () => void;
}

export function Card({ title, children, className, onClick }: CardProps) {
  const classes = [onClick && 'card--clickable', className].filter(Boolean).join(' ');
  return (
    <GtCard
      className={classes || undefined}
      onClick={onClick}
    >
      {title && <div className="card__header">{title}</div>}
      <div className="card__body">{children}</div>
    </GtCard>
  );
}
