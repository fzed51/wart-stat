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

    // Validation du contenu (2000-3000 caractères)
    if (formData.content.length < 2000) {
      setValidationError(`Le rapport doit contenir au moins 2000 caractères (actuellement: ${formData.content.length})`);
      return;
    }
    if (formData.content.length > 3000) {
      setValidationError(`Le rapport ne doit pas dépasser 3000 caractères (actuellement: ${formData.content.length})`);
      return;
    }

    try {
      await addReport(formData);
      navigate('/');
    } catch {
      // L'erreur est gérée par le store
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{ maxWidth: '600px', margin: '0 auto' }}>
      <h1>Nouveau Rapport</h1>

      {(error || validationError) && (
        <div style={{ color: 'red', marginBottom: '1rem' }}>
          {validationError || error}
        </div>
      )}

      <div style={{ marginBottom: '1rem' }}>
        <label htmlFor="country">Pays :</label>
        <select
          id="country"
          value={formData.country}
          onChange={(e) => setFormData({ ...formData, country: e.target.value as Country })}
          required
          style={{ display: 'block', width: '100%', padding: '0.5rem', marginTop: '0.25rem' }}
        >
          {COUNTRIES.map((c) => (
            <option key={c.value} value={c.value}>
              {c.label}
            </option>
          ))}
        </select>
      </div>

      <div style={{ marginBottom: '1rem' }}>
        <label htmlFor="date">Date :</label>
        <input
          type="date"
          id="date"
          value={formData.date}
          onChange={(e) => setFormData({ ...formData, date: e.target.value })}
          required
          style={{ display: 'block', width: '100%', padding: '0.5rem', marginTop: '0.25rem' }}
        />
      </div>

      <div style={{ marginBottom: '1rem' }}>
        <label htmlFor="time">Heure :</label>
        <input
          type="time"
          id="time"
          value={formData.time}
          onChange={(e) => setFormData({ ...formData, time: e.target.value })}
          required
          style={{ display: 'block', width: '100%', padding: '0.5rem', marginTop: '0.25rem' }}
        />
      </div>

      <div style={{ marginBottom: '1rem' }}>
        <label htmlFor="content">
          Rapport ({formData.content.length}/3000 caractères, min. 2000) :
        </label>
        <textarea
          id="content"
          value={formData.content}
          onChange={(e) => setFormData({ ...formData, content: e.target.value })}
          required
          rows={15}
          style={{
            display: 'block',
            width: '100%',
            padding: '0.5rem',
            marginTop: '0.25rem',
            resize: 'vertical',
          }}
        />
      </div>

      <div style={{ display: 'flex', gap: '1rem' }}>
        <button
          type="button"
          onClick={() => navigate('/')}
          style={{ padding: '0.5rem 1rem' }}
        >
          Annuler
        </button>
        <button
          type="submit"
          disabled={isLoading}
          style={{ padding: '0.5rem 1rem' }}
        >
          {isLoading ? 'Enregistrement...' : 'Enregistrer'}
        </button>
      </div>
    </form>
  );
}
