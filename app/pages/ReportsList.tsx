import { useCallback, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReportStore } from '../stores/reportStore';
import { getCountryLabel } from '../constants/countries';
import { PageHeader, Alert, EmptyState, Button, Table, TableHead, TableBody, TableRow, TableCell } from '../components/common';

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

  return (
    <div className="page">
      <PageHeader
        title="Liste des Rapports"
        subtitle={`${reports.length} rapport${reports.length !== 1 ? 's' : ''} enregistré${reports.length !== 1 ? 's' : ''}`}
      />

      {isLoading && (
        <Alert variant="info">Chargement des rapports...</Alert>
      )}

      {error && (
        <Alert variant="error">{error}</Alert>
      )}

      {!isLoading && reports.length === 0 ? (
        <EmptyState
          icon="∅"
          title="Aucun rapport"
          description="Aucun rapport enregistré pour l'instant."
          action={
            <Button variant="primary" onClick={() => navigate('/reports/add')}>
              Créer un rapport
            </Button>
          }
        />
      ) : (
        <Table isLoading={isLoading}>
          <TableHead>
            <TableRow>
              <TableCell isHeader align="center">ID</TableCell>
              <TableCell isHeader>Pays</TableCell>
              <TableCell isHeader>Date</TableCell>
              <TableCell isHeader>Heure</TableCell>
              <TableCell isHeader align="center">W/L</TableCell>
              <TableCell isHeader>Mission</TableCell>
              <TableCell isHeader>Carte</TableCell>
              <TableCell isHeader align="right">Temps</TableCell>
              <TableCell isHeader align="right">Points</TableCell>
              <TableCell isHeader align="right">SL</TableCell>
              <TableCell isHeader align="right">RP</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {reports.map((report) => (
              <TableRow
                key={report.report_id}
                onClick={() => navigate(`/reports/${report.report_id}`)}
                style={{ cursor: 'pointer' }}
              >
                <TableCell align="center">{report.report_id}</TableCell>
                <TableCell>{getCountryLabel(report.country)}</TableCell>
                <TableCell>{formatDate(report.datetime)}</TableCell>
                <TableCell>{formatTime(report.datetime)}</TableCell>
                <TableCell align="center">{WonLostRender(report.win_lost)}</TableCell>
                <TableCell>{report.mission_type}</TableCell>
                <TableCell>{report.carte}</TableCell>
                <TableCell align="right">{report.temps_jeux}s</TableCell>
                <TableCell align="right">{report.points_totaux.toLocaleString('fr-FR')}</TableCell>
                <TableCell align="right">{report.total_sl.toLocaleString('fr-FR')}</TableCell>
                <TableCell align="right">{report.total_rp.toLocaleString('fr-FR')}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </div>
  );
}
