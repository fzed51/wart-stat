import './Table.css';

interface TableProps {
  children: React.ReactNode;
  className?: string;
  isLoading?: boolean;
  style?: React.CSSProperties;
}

interface TableHeadProps {
  children: React.ReactNode;
}

interface TableBodyProps {
  children: React.ReactNode;
}

interface TableRowProps {
  children: React.ReactNode;
  onClick?: () => void;
  className?: string;
}

interface TableCellProps {
  children: React.ReactNode;
  align?: 'left' | 'center' | 'right';
  className?: string;
  isHeader?: boolean;
}

export function Table({ children, className, isLoading, style }: TableProps) {
  return (
    <div className={`table-container${isLoading ? ' loading' : ''}${className ? ` ${className}` : ''}`} style={style}>
      <table>{children}</table>
    </div>
  );
}

export function TableHead({ children }: TableHeadProps) {
  return <thead>{children}</thead>;
}

export function TableBody({ children }: TableBodyProps) {
  return <tbody>{children}</tbody>;
}

export function TableRow({ children, onClick, className }: TableRowProps) {
  return (
    <tr onClick={onClick} className={className} style={onClick ? { cursor: 'pointer' } : {}}>
      {children}
    </tr>
  );
}

export function TableCell({ children, align = 'left', className, isHeader }: TableCellProps) {
  const alignClass = align !== 'left' ? ` ${align}` : '';
  const Element = isHeader ? 'th' : 'td';

  return (
    <Element className={`${alignClass}${className ? ` ${className}` : ''}`}>
      {children}
    </Element>
  );
}
