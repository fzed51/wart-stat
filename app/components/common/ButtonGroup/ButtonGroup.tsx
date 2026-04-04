import './ButtonGroup.css';

interface ButtonGroupProps {
  children: React.ReactNode;
  vertical?: boolean;
  className?: string;
}

export function ButtonGroup({ children, vertical, className }: ButtonGroupProps) {
  return (
    <div
      className={`button-group${vertical ? ' button-group--vertical' : ''}${
        className ? ` ${className}` : ''
      }`}
    >
      {children}
    </div>
  );
}
