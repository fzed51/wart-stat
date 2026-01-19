import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReportStore } from '../stores/reportStore';

const countryNames: Record<string, string> = {
  US: 'États-Unis',
  GER: 'Allemagne',
  URRS: 'URSS',
  UK: 'Royaume-Uni',
  JAP: 'Japon',
  CH: 'Chine',
  IT: 'Italie',
  FR: 'France',
  SU: 'Suède',
  IS: 'Islande',
};

const formatDate = (isoString: string): string => {
  const date = new Date(isoString);
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

const formatTime = (isoString: string): string => {
  const date = new Date(isoString);
  return date.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

export default function ReportsList() {
  const navigate = useNavigate();
  const { reports, isLoading, error, fetchReports } = useReportStore();

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  if (isLoading) {
    return (
      <div className="page">
        <div className="loading">Chargement des rapports</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="page">
        <div className="error-message">{error}</div>
      </div>
    );
  }

  return (
    <div className="page">
      <div className="page-header">
        <h1>Liste des Rapports</h1>
        <p>{reports.length} rapport{reports.length !== 1 ? 's' : ''} enregistré{reports.length !== 1 ? 's' : ''}</p>
      </div>

      {reports.length === 0 ? (
        <div className="empty-state">
          <p>Aucun rapport enregistré.</p>
          <button onClick={() => navigate('/reports/add')}>
            Créer un rapport
          </button>
        </div>
      ) : (
        <div className="table-container">
          <table className="reports-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Pays</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Contenu</th>
              </tr>
            </thead>
            <tbody>
              {reports.map((report) => (
                <tr key={report.id}>
                  <td className="table-id">{report.id}</td>
                  <td className="table-country">{countryNames[report.country] || report.country}</td>
                  <td className="table-date">{formatDate(report.datetime)}</td>
                  <td className="table-time">{formatTime(report.datetime)}</td>
                  <td className="table-content">
                    <div className="content-preview">
                      {report.content}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
