import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getCountryLabel, type Country } from '../constants/countries';
import { FormattedTime } from '../components/FormattedTime';
import { Alert, Button } from '../components/common';
import { CountrySelect } from '../components/ContrySelect';
import { useReportStore, type ReportUpdateData } from '../stores/reportStore';

interface Action {
  id: number;
  mission_id: number;
  type_action: string;
  timestamp_sec: number;
  vehicle_name: string;
  weapon_used: string | null;
  target_name: string | null;
  point_score: number;
  sl_awarded: number;
  rp_awarded: number;
}



function ActionGroup({ type, actions }: { type: string; actions: Action[] }) {
  if (actions.length === 0) return null;

  const totalSL = actions.reduce((sum, a) => sum + a.sl_awarded, 0);
  const totalRP = actions.reduce((sum, a) => sum + a.rp_awarded, 0);
  const totalPoints = actions.reduce((sum, a) => sum + a.point_score, 0);

  return (
    <div className="action-group">
      <div className="action-group-header">
        <span className="action-type">{type}</span>
        <span className="action-count">{actions.length}</span>
        <span className="action-totals">
          {totalPoints} pts • {totalSL.toLocaleString('fr-FR')} SL • {totalRP.toLocaleString('fr-FR')} RP
        </span>
      </div>
      <div className="action-items">
        {actions.map((action) => (
          <div key={action.id} className="action-item">
            <div className="action-time"><FormattedTime seconds={action.timestamp_sec} /></div>
            <div className="action-vehicle">{action.vehicle_name}</div>
            <div className="action-weapon">{action.weapon_used || '-'}</div>
            <div className="action-target">{action.target_name || '-'}</div>
            <div className="action-points">{action.point_score} pts</div>
            <div className="action-rewards">
              {action.sl_awarded.toLocaleString('fr-FR')} SL • {action.rp_awarded.toLocaleString('fr-FR')} RP
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default function ReportDetail() {
  const navigate = useNavigate();
  const params = useParams<{ id: string }>();
  const reportId = parseInt(params.id || '0', 10);

  const {
    reportDetail: data,
    isLoading,
    error,
    fetchReportDetail,
    updateReport,
    isLoading: isUpdating
  } = useReportStore();

  const [localError, setLocalError] = useState<string | null>(null);
  const [isEditing, setIsEditing] = useState(false);
  const [editData, setEditData] = useState<{ country: Country; date: string; time: string }>({
    country: 'FR',
    date: '',
    time: '',
  });

  useEffect(() => {
    if (data === null || data.report.id !== reportId) {
      fetchReportDetail(reportId)
    }
  }, [reportId, data, fetchReportDetail]);

  useEffect(() => {
    if (data) {
      setEditData({ 
        country: data.report.country as Country, 
        date: new Date(data.report.datetime).toISOString().substring(0, 10),
        time: new Date(data.report.datetime).toISOString().substring(11, 16)
      });
    } else {
      setEditData({ country: 'FR', date: '', time: '' });
    }
  }, [data]);

  const handleSaveEdit = async () => {
    try {
      const updatePayload: ReportUpdateData = {
        country: editData.country,
        datetime: new Date(`${editData.date}T${editData.time}`).toISOString(),
      };

      await updateReport(reportId, updatePayload);

      // Update local data
      if (data) {
        const updatedReport = { ...data.report };
        updatedReport.country = editData.country as any;
        updatedReport.datetime = updatePayload.datetime || '';
        setEditData({
          country: editData.country,
          date: editData.date,
          time: editData.time,
        });
      }

      setIsEditing(false);
    } catch (err) {
      setLocalError(err instanceof Error ? err.message : 'Erreur lors de la mise à jour');
    }
  };

  if (isLoading) {
    return (
      <div className="page">
        <Alert variant="info">Chargement du rapport...</Alert>
      </div>
    );
  }

  if (error || !data || data.report.id !== reportId) {
    return (
      <div className="page">
        <Alert variant="error">{localError || error || 'Rapport non trouvé'}</Alert>
        <Button onClick={() => navigate('/reports')} style={{ marginTop: '1rem' }}>
          Retour à la liste
        </Button>
      </div>
    );
  }

  const { report, mission, actions, bonuses } = data;

  // Group actions by type
  const actionsByType: Record<string, Action[]> = {};
  actions.forEach((action) => {
    if (!actionsByType[action.type_action]) {
      actionsByType[action.type_action] = [];
    }
    actionsByType[action.type_action].push(action);
  });

  // Sort action types by appearance order
  const sortedActionTypes = Object.keys(actionsByType);

  // Calculate totals
  const totalSL = actions.reduce((sum, a) => sum + a.sl_awarded, 0) + bonuses.reduce((sum, b) => sum + b.sl_awarded, 0);
  const totalRP = actions.reduce((sum, a) => sum + a.rp_awarded, 0) + bonuses.reduce((sum, b) => sum + b.rp_awarded, 0);

  const resultIcon = mission.result?.toLowerCase() === 'victoire' ? '✓' : '✗';
  const resultClass = mission.result?.toLowerCase() === 'victoire' ? 'result-win' : 'result-lost';

  return (
    <div className="page report-detail">
      <div className="report-header">
        <button onClick={() => navigate('/reports')} className="back-button">
          ← Retour
        </button>
        <div className="report-title">
          <span className={`result-badge ${resultClass}`}>{resultIcon}</span>
          <h1>Rapport #{report.id}</h1>
          <button
            onClick={() => setIsEditing(!isEditing)}
            className="edit-button"
            title="Éditer le rapport"
          >
            {isEditing ? '✕' : '✎'}
          </button>
        </div>
        <div className="report-meta">
          <div className="meta-item">
            <span className="label">Pays:</span>
            <span className="value">{getCountryLabel(report.country)}</span>
          </div>
          <div className="meta-item">
            <span className="label">Date:</span>
            <span className="value">{new Date(report.datetime).toLocaleDateString('fr-FR')}</span>
          </div>
          <div className="meta-item">
            <span className="label">Heure:</span>
            <span className="value">{new Date(report.datetime).toLocaleTimeString('fr-FR')}</span>
          </div>
          <div className="meta-item">
            <span className="label">Session:</span>
            <span className="value mono" title={report.session_id}>
              {report.session_id?.substring(0, 8)}...
            </span>
          </div>
        </div>
      </div>

      {isEditing && (
        <div className="report-edit-form">
          {(localError) && (
            <Alert variant="error">{localError}</Alert>
          )}
          <div className="edit-form-content">
            <div className="form-group">
              <CountrySelect
                value={editData.country}
                onChange={(country) => setEditData({ ...editData, country })}
              />
            </div>

            <div className="form-group">
              <label htmlFor="edit-date">Date</label>
              <input
                id="edit-date"
                type="date"
                value={editData.date}
                onChange={(e) => setEditData({ ...editData, date: e.target.value })}
                className="input-field"
              />
            </div>

            <div className="form-group">
              <label htmlFor="edit-time">Heure</label>
              <input
                id="edit-time"
                type="time"
                value={editData.time}
                onChange={(e) => setEditData({ ...editData, time: e.target.value })}
                className="input-field"
              />
            </div>

            <div className="form-actions">
              <Button
                onClick={() => setIsEditing(false)}
                variant="ghost"
                disabled={isUpdating}
              >
                Annuler
              </Button>
              <Button
                onClick={handleSaveEdit}
                variant="primary"
                disabled={isUpdating}
              >
                {isUpdating ? 'Sauvegarde...' : 'Sauvegarder'}
              </Button>
            </div>
          </div>
        </div>
      )}

      <div className="report-body">
        {/* Mission Header */}
        <div className="mission-header">
          <h2 className={resultClass}>
            {mission.result} en [{mission.mission_type}] {mission.location}
          </h2>
          <div className="mission-stats">
            <div className="stat">
              <span className="stat-label">Durée:</span>
              <span className="stat-value"><FormattedTime seconds={mission.mission_duration_sec} /></span>
            </div>
            <div className="stat">
              <span className="stat-label">Activité:</span>
              <span className="stat-value">{mission.activity_pct}%</span>
            </div>
          </div>
        </div>

        {/* Actions by Type */}
        <div className="actions-section">
          <h3>Déroulement de la mission</h3>
          {sortedActionTypes.map((type) => (
            <ActionGroup key={type} type={type} actions={actionsByType[type]} />
          ))}
        </div>

        {/* Bonuses */}
        {bonuses.length > 0 && (
          <div className="bonuses-section">
            <h3>Bonus et Prix ({bonuses.length})</h3>
            <div className="bonus-items">
              {bonuses.map((bonus) => (
                <div key={bonus.id} className="bonus-item">
                  <div className="bonus-time"><FormattedTime seconds={bonus.timestamp_sec} /></div>
                  <div className="bonus-name">{bonus.bonus_name}</div>
                  <div className="bonus-rewards">
                    {bonus.sl_awarded.toLocaleString('fr-FR')} SL • {bonus.rp_awarded.toLocaleString('fr-FR')} RP
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Summary */}
        <div className="summary-section">
          <h3>Résumé</h3>
          <div className="summary-grid">
            <div className="summary-item">
              <span className="summary-label">SL gagnés:</span>
              <span className="summary-value">{totalSL.toLocaleString('fr-FR')}</span>
            </div>
            <div className="summary-item">
              <span className="summary-label">RP gagnés:</span>
              <span className="summary-value">{totalRP.toLocaleString('fr-FR')}</span>
            </div>
            <div className="summary-item">
              <span className="summary-label">Coûts réparation:</span>
              <span className="summary-value negative">{mission.repair_cost.toLocaleString('fr-FR')}</span>
            </div>
            <div className="summary-item">
              <span className="summary-label">Munitions/Équipage:</span>
              <span className="summary-value negative">{mission.ammo_crew_cost.toLocaleString('fr-FR')}</span>
            </div>
            <div className="summary-item highlight">
              <span className="summary-label">Gagné final:</span>
              <span className="summary-value">{mission.earned_final.toLocaleString('fr-FR')} SL</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
