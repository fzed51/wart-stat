import { Link } from 'react-router-dom';
import { version } from "../../package.json"

export default function Home() {
  return (
    <div className="home-page">
      <h1>Wart-Stat</h1>
      <p className="home-subtitle">Système de rapports de guerre</p>
      
      <div className="home-terminal">
        <div className="terminal-line">Status: ONLINE</div>
        <div className="terminal-line">Version: {version}</div>
        <div className="terminal-line">Dernière mise à jour: 22/12/2025</div>
        <div className="terminal-line terminal-prompt">_</div>
      </div>
      
      <div className="home-actions">
        <Link to="/reports/add">
          <button className="primary">Ajouter un rapport</button>
        </Link>
      </div>
    </div>
  );
}
