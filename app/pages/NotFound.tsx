import { Link } from 'react-router-dom';

export default function NotFound() {
  return (
    <div className="not-found">
      <h1>404</h1>
      <p>Page non trouvée dans le système</p>
      
      <div className="not-found-code">
        <div>ERROR_CODE: PAGE_NOT_FOUND</div>
        <div>STATUS: 404</div>
        <div>MESSAGE: La ressource demandée n'existe pas</div>
        <div>TIMESTAMP: {new Date().toISOString()}</div>
      </div>
      
      <Link to="/">
        <button>Retour à l'accueil</button>
      </Link>
    </div>
  );
}
