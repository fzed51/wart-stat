import { Link } from 'react-router-dom';
import { Button, Terminal, TerminalLine } from '../components/common';

export default function NotFound() {
  return (
    <div className="page" style={{ textAlign: 'center', paddingTop: '3rem' }}>
      <h1 style={{ fontSize: '3rem', marginBottom: '0.5rem' }}>404</h1>
      <p style={{ marginBottom: '2rem', color: 'var(--color-text-muted)' }}>
        Page non trouvée dans le système
      </p>
      
      <Terminal title="ERROR LOG" style={{ maxWidth: '500px', margin: '2rem auto' }}>
        <TerminalLine>ERROR_CODE: PAGE_NOT_FOUND</TerminalLine>
        <TerminalLine>STATUS: 404</TerminalLine>
        <TerminalLine>MESSAGE: La ressource demandée n'existe pas</TerminalLine>
        <TerminalLine>TIMESTAMP: {new Date().toISOString()}</TerminalLine>
      </Terminal>

      <div style={{ marginTop: '2rem' }}>
        <Link to="/">
          <Button variant="primary">Retour à l'accueil</Button>
        </Link>
      </div>
    </div>
  );
}
