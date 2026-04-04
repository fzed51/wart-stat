import '../reset.css';
import '../base.css';
import './AppShell.css';

interface AppShellProps {
  children: React.ReactNode;
  className?: string;
}

export function AppShell({ children, className }: AppShellProps) {
  return (
    <div className={`app-shell${className ? ` ${className}` : ''}`}>
      {children}
    </div>
  );
}
