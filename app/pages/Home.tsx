import { Link } from 'react-router-dom';

export default function Home() {
  return (
    <div style={{ padding: '2rem' }}>
      <h1>Accueil Wart-Stat</h1>
      <nav style={{ marginTop: '2rem' }}>
        <Link to="/reports/add">
          <button style={{ padding: '0.75rem 1.5rem', fontSize: '1rem' }}>
            Ajouter un rapport
          </button>
        </Link>
      </nav>
    </div>
  );
}
