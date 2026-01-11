import { Link } from 'react-router-dom';

export default function Home() {
  return (
    <div className="home-page">
      <h1><Wart-Stat2></Wart-Stat2></h1>
      <p className="home-subtitle">Système de rapports de guerre</p>
      
      <div className="home-terminal">
        <div className="terminal-line">Status: ONLINE</div>
        <div className="terminal-line">Version: 1.0.0</div>
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
