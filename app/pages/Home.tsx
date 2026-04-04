import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { version } from "../../package.json"
import { useReportStore } from '../stores/reportStore';
import { getCountryLabel } from '../constants/countries';
import { Button, Card, Badge, Terminal, TerminalLine, Separator } from '../components/common';

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
    <div className="page">
      <h1 style={{ textAlign: 'center', marginBottom: '0.5rem' }}>Wart-Stat</h1>
      <p style={{ textAlign: 'center', color: 'var(--color-text-muted)', marginBottom: '2rem' }}>
        Système de rapports de guerre
      </p>
      
      <Terminal title="SYSTEM STATUS" style={{ maxWidth: '600px', margin: '0 auto 2rem' }}>
        <TerminalLine>Status: ONLINE</TerminalLine>
        <TerminalLine>Version: {version}</TerminalLine>
        <TerminalLine>Dernière mise à jour: 22/12/2025</TerminalLine>
        <TerminalLine prompt>_</TerminalLine>
      </Terminal>
      
      <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
        <Link to="/reports/add">
          <Button variant="primary">Ajouter un rapport</Button>
        </Link>
      </div>

      {recentReports.length > 0 && (
        <>
          <Separator label="Derniers rapports" />
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1.5rem', marginBottom: '2rem' }}>
            {recentReports.map((report) => (
              <Card
                key={report.report_id}
                title={`Rapport #${report.report_id}`}
                onClick={() => navigate(`/reports/${report.report_id}`)}
              >
                <div style={{ marginBottom: '1rem' }}>
                  <Badge variant={report.win_lost?.toLowerCase() === 'victoire' ? 'success' : 'error'}>
                    {report.win_lost?.toLowerCase() === 'victoire' ? '✓ Victoire' : '✗ Défaite'}
                  </Badge>
                </div>
                <div style={{ fontSize: '0.9rem', display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ color: 'var(--color-text-muted)' }}>Pays:</span>
                    <span>{getCountryLabel(report.country)}</span>
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ color: 'var(--color-text-muted)' }}>Mission:</span>
                    <span>{report.mission_type}</span>
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ color: 'var(--color-text-muted)' }}>Carte:</span>
                    <span>{report.carte}</span>
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <span style={{ color: 'var(--color-text-muted)' }}>Points:</span>
                    <span>{report.points_totaux.toLocaleString('fr-FR')}</span>
                  </div>
                </div>
                <div style={{ marginTop: '1rem', paddingTop: '1rem', borderTop: '1px solid var(--color-border)', fontSize: '0.8rem', display: 'flex', justifyContent: 'space-between', color: 'var(--color-text-muted)' }}>
                  <span>{formatDate(report.datetime)}</span>
                  <span>{formatTime(report.datetime)}</span>
                </div>
              </Card>
            ))}
          </div>
          <div style={{ textAlign: 'center' }}>
            <Link to="/reports">
              <Button variant="ghost">Voir tous les rapports →</Button>
            </Link>
          </div>
        </>
      )}
    </div>
  );
}
