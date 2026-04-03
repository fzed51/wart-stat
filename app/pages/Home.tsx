import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { version } from "../../package.json"
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

export default function Home() {
  const navigate = useNavigate();
  const { reports, fetchReports } = useReportStore();

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  // Get 3 most recent reports
  const recentReports = reports.slice(0, 3);

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

      {recentReports.length > 0 && (
        <div className="recent-reports">
          <h2>Derniers rapports</h2>
          <div className="recent-reports-list">
            {recentReports.map((report) => (
              <div
                key={report.report_id}
                className="recent-report-card"
                onClick={() => navigate(`/reports/${report.report_id}`)}
              >
                <div className="report-card-header">
                  <span className="report-id">#{report.report_id}</span>
                  <span className={`report-result ${report.win_lost?.toLowerCase() === 'victoire' ? 'win' : 'lost'}`}>
                    {report.win_lost?.toLowerCase() === 'victoire' ? '✓ Victoire' : '✗ Défaite'}
                  </span>
                </div>
                <div className="report-card-body">
                  <div className="card-row">
                    <span className="card-label">Pays:</span>
                    <span className="card-value">{getCountryLabel(report.country)}</span>
                  </div>
                  <div className="card-row">
                    <span className="card-label">Mission:</span>
                    <span className="card-value">{report.mission_type}</span>
                  </div>
                  <div className="card-row">
                    <span className="card-label">Carte:</span>
                    <span className="card-value">{report.carte}</span>
                  </div>
                  <div className="card-row">
                    <span className="card-label">Points:</span>
                    <span className="card-value">{report.points_totaux.toLocaleString('fr-FR')}</span>
                  </div>
                </div>
                <div className="report-card-footer">
                  <span className="report-date">{formatDate(report.datetime)}</span>
                  <span className="report-time">{formatTime(report.datetime)}</span>
                </div>
              </div>
            ))}
          </div>
          <div className="view-all-link">
            <Link to="/reports">Voir tous les rapports →</Link>
          </div>
        </div>
      )}
    </div>
  );
}
