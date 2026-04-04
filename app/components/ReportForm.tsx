import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useReportStore, type ReportFormData } from '../stores/reportStore';
import { usePreferencesStore } from '../stores/preferencesStore';
import { CountrySelect } from './ContrySelect';
import { Alert, Button, ButtonGroup, Input, Textarea, PageHeader } from './common';

const getDefaultDate = (): string => {
  return new Date().toISOString().split('T')[0];
};

const getDefaultTime = (): string => {
  return new Date().toTimeString().slice(0, 5);
};

export default function ReportForm() {
  const navigate = useNavigate();
  const { addReport, isLoading, error } = useReportStore();
  const { lastCountry, updateLastCountry } = usePreferencesStore();

  const [formData, setFormData] = useState<ReportFormData>({
    country: lastCountry || 'FR',
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
      updateLastCountry(formData.country);
      navigate('/');
    } catch {
      // L'erreur est gérée par le store
    }
  };

  return (
    <div className="page">
      <PageHeader title="Nouveau Rapport" subtitle="Ajoutez un nouveau rapport de mission" />

      <form onSubmit={handleSubmit} style={{ maxWidth: '600px' }}>
        {(error || validationError) && (
          <Alert variant="error">{validationError || error}</Alert>
        )}

        <CountrySelect
          value={formData.country}
          onChange={(country) => setFormData({ ...formData, country })}
        />

        <Input
          type="date"
          label="Date"
          id="date"
          value={formData.date}
          onChange={(e) => setFormData({ ...formData, date: e.target.value })}
          required
        />

        <Input
          type="time"
          label="Heure"
          id="time"
          value={formData.time}
          onChange={(e) => setFormData({ ...formData, time: e.target.value })}
          required
        />

        <Textarea
          label="Contenu du rapport"
          id="content"
          value={formData.content}
          onChange={(e) => setFormData({ ...formData, content: e.target.value })}
          required
          rows={15}
          placeholder="Saisissez le contenu de votre rapport ici..."
          showCounter
        />

        <ButtonGroup>
          <Button variant="ghost" type="button" onClick={() => navigate('/')}>
            Annuler
          </Button>
          <Button variant="primary" type="submit" disabled={isLoading}>
            {isLoading ? 'Enregistrement...' : 'Enregistrer'}
          </Button>
        </ButtonGroup>
      </form>
    </div>
  );
}
