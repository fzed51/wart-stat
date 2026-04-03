import { useCallback, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReportStore } from '../stores/reportStore';
import { getCountryLabel } from '../constants/countries';

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

  const WonLostRender = useCallback((result: string) => {
    const style = {width: '2em', height: '2em'};
    let className = 'result-lost';
    let src = '/picto/skull.svg';
    let alt = 'Défaite';
    if (result.toLowerCase() === 'victoire') {
      className = 'result-win';
      src = '/picto/military-medal.svg';
      alt = 'Victoire';
      
    }
    return <img {...{className, src, alt, style}} />;
  }, []);

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
                <th>W/L</th>
                <th>Mission</th>
                <th>Carte</th>
                <th>Temps (s)</th>
                <th>Points</th>
                <th>SL</th>
                <th>RP</th>
              </tr>
            </thead>
            <tbody>
              {reports.map((report) => (
                <tr key={report.report_id} onClick={() => navigate(`/reports/${report.report_id}`)} className="clickable-row">
                  <td className="table-id"><span title={
                    report.session_id || "-"
                  } className="id-link">{report.report_id}</span></td>
                  <td className="table-country">{getCountryLabel(report.country)}</td>
                  <td className="table-date">{formatDate(report.datetime)}</td>
                  <td className="table-time">{formatTime(report.datetime)}</td>
                  <td className="table-result">{WonLostRender(report.win_lost)}</td>
                  <td className="table-mission">{report.mission_type}</td>
                  <td className="table-carte">{report.carte}</td>
                  <td className="table-duration">{report.temps_jeux}</td>
                  <td className="table-points">{report.points_totaux.toLocaleString('fr-FR')}</td>
                  <td className="table-sl">{report.total_sl.toLocaleString('fr-FR')}</td>
                  <td className="table-rp">{report.total_rp.toLocaleString('fr-FR')}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
