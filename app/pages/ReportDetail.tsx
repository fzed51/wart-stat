import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { getCountryLabel } from '../constants/countries';
import { PageHeader, Alert, Button, Badge, Card, Separator } from '../components/common';

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

interface Bonus {
  id: number;
  mission_id: number;
  bonus_name: string;
  timestamp_sec: number;
  sl_awarded: number;
  rp_awarded: number;
}

interface Mission {
  id: number;
  report_id: number;
  mission_type: string;
  location: string;
  result: string;
  mission_duration_sec: number;
  session_id: string;
  total_sl: number;
  total_crp: number;
  total_rp: number;
  activity_pct: number;
  repair_cost: number;
  ammo_crew_cost: number;
  victory_reward: number;
  participation_reward: number;
  earned_final: number;
}

interface Report {
  id: number;
  country: string;
  datetime: string;
  session_id: string;
  content: string;
}

interface ReportData {
  report: Report;
  mission: Mission;
  actions: Action[];
  bonuses: Bonus[];
}

const formatTime = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  }
  return `${minutes}:${String(secs).padStart(2, '0')}`;
};

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
            <div className="action-time">{formatTime(action.timestamp_sec)}</div>
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

  const [data, setData] = useState<ReportData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchReport = async () => {
      try {
        setLoading(true);
        const response = await fetch(`/api/reports/${reportId}`);
        if (!response.ok) {
          throw new Error('Rapport non trouvé');
        }
        const result = await response.json();
        setData(result);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Erreur lors du chargement');
      } finally {
        setLoading(false);
      }
    };

    fetchReport();
  }, [reportId]);

  if (loading) {
    return (
      <div className="page">
        <Alert variant="info">Chargement du rapport...</Alert>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="page">
        <Alert variant="error">{error || 'Rapport non trouvé'}</Alert>
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

      <div className="report-body">
        {/* Mission Header */}
        <div className="mission-header">
          <h2 className={resultClass}>
            {mission.result} en [{mission.mission_type}] {mission.location}
          </h2>
          <div className="mission-stats">
            <div className="stat">
              <span className="stat-label">Durée:</span>
              <span className="stat-value">{formatTime(mission.mission_duration_sec)}</span>
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
                  <div className="bonus-time">{formatTime(bonus.timestamp_sec)}</div>
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
