import './Terminal.css';

interface TerminalProps {
  title?: string;
  children: React.ReactNode;
  className?: string;
}

interface TerminalLineProps {
  prompt?: boolean;
  children: React.ReactNode;
}

export function Terminal({ title = 'TERMINAL', children, className }: TerminalProps) {
  return (
    <div
      className={`terminal${className ? ` ${className}` : ''}`}
      data-title={title}
    >
      {children}
    </div>
  );
}

export function TerminalLine({ prompt = false, children }: TerminalLineProps) {
  return (
    <div className={`terminal__line${prompt ? ' terminal__line--prompt' : ''}`}>
      {children}
    </div>
  );
}
