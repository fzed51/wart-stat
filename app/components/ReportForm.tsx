import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReportStore, type Country, type ReportFormData } from '../stores/reportStore';

const COUNTRIES: { value: Country; label: string }[] = [
  { value: 'US', label: 'États-Unis' },
  { value: 'GER', label: 'Allemagne' },
  { value: 'URRS', label: 'URSS' },
  { value: 'UK', label: 'Royaume-Uni' },
  { value: 'JAP', label: 'Japon' },
  { value: 'CH', label: 'Chine' },
  { value: 'IT', label: 'Italie' },
  { value: 'FR', label: 'France' },
  { value: 'SU', label: 'Suède' },
  { value: 'IS', label: 'Israël' },
];

const getDefaultDate = (): string => {
  return new Date().toISOString().split('T')[0];
};

const getDefaultTime = (): string => {
  return new Date().toTimeString().slice(0, 5);
};

export default function ReportForm() {
  const navigate = useNavigate();
  const { addReport, isLoading, error } = useReportStore();

  const [formData, setFormData] = useState<ReportFormData>({
    country: 'FR',
    date: getDefaultDate(),
    time: getDefaultTime(),
    content: '',
  });

  const [validationError, setValidationError] = useState<string | null>(null);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setValidationError(null);

    try {
      await addReport(formData);
      navigate('/');
    } catch {
      // L'erreur est gérée par le store
    }
  };

  return (
    <form onSubmit={handleSubmit} className="report-form">
      <h1>Nouveau Rapport</h1>

      {(error || validationError) && (
        <div className="error-message">
          {validationError || error}
        </div>
      )}

      <div className="form-group">
        <label htmlFor="country">Pays</label>
        <select
          id="country"
          value={formData.country}
          onChange={(e) => setFormData({ ...formData, country: e.target.value as Country })}
          required
        >
          {COUNTRIES.map((c) => (
            <option key={c.value} value={c.value}>
              {c.label}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label htmlFor="date">Date</label>
        <input
          type="date"
          id="date"
          value={formData.date}
          onChange={(e) => setFormData({ ...formData, date: e.target.value })}
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor="time">Heure</label>
        <input
          type="time"
          id="time"
          value={formData.time}
          onChange={(e) => setFormData({ ...formData, time: e.target.value })}
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor="content">
          Contenu du rapport
        </label>
        <textarea
          id="content"
          value={formData.content}
          onChange={(e) => setFormData({ ...formData, content: e.target.value })}
          required
          rows={15}
          placeholder="Saisissez le contenu de votre rapport ici..."
        />
        <div className="char-counter valid">
          {formData.content.length} caractères
        </div>
      </div>

      <div className="button-group">
        <button
          type="button"
          onClick={() => navigate('/')}
        >
          Annuler
        </button>
        <button
          type="submit"
          disabled={isLoading}
          className="primary"
        >
          {isLoading ? 'Enregistrement...' : 'Enregistrer'}
        </button>
      </div>
    </form>
  );
}
