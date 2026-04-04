import './EmptyState.css';

interface EmptyStateProps {
  icon?: string;
  title?: string;
  description?: string;
  action?: React.ReactNode;
  className?: string;
  style?: React.CSSProperties;
}

export function EmptyState({
  icon = '∅',
  title = 'Aucune donnée',
  description,
  action,
  className,
  style,
}: EmptyStateProps) {
  return (
    <div className={`empty-state${className ? ` ${className}` : ''}`} style={style}>
      <div className="empty-state__icon">{icon}</div>
      <h3 className="empty-state__title">{title}</h3>
      {description && <p className="empty-state__description">{description}</p>}
      {action && <div className="empty-state__action">{action}</div>}
    </div>
  );
}
