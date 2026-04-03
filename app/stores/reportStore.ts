import { create } from 'zustand';
import { type Country } from '../constants/countries';

// Réexporte le type Country pour maintenir la compatibilité avec les imports existants
export type { Country } from '../constants/countries';

export interface Report {
  id?: number;
  country: Country;
  datetime: string; // ISO format
  content: string;
}


export interface ReportDetail {
  report_id: number;
  country: Country;
  datetime: string; // ISO format
  session_id: string | null;
  win_lost: string;
  mission_type: string;
  carte: string;
  temps_jeux: number;
  points_totaux: number;
  total_sl: number;
  total_rp: number;
}

export interface ReportFormData {
  country: Country;
  date: string;
  time: string;
  content: string;
}

interface ReportState {
  reports: ReportDetail[];
  isLoading: boolean;
  error: string | null;
  fetchReports: () => Promise<void>;
  addReport: (formData: ReportFormData) => Promise<void>;
}

const combineDateTime = (date: string, time: string): string => {
  return new Date(`${date}T${time}`).toISOString();
};

export const useReportStore = create<ReportState>((set) => ({
  reports: [],
  isLoading: false,
  error: null,

  fetchReports: async () => {
    set({ isLoading: true, error: null });

    try {
      const response = await fetch('/api/reports');

      if (!response.ok) {
        throw new Error('Erreur lors de la récupération des rapports');
      }

      const reports = await response.json();
      set({ reports, isLoading: false });
    } catch (error) {
      set({
        error: error instanceof Error ? error.message : 'Une erreur est survenue',
        isLoading: false,
      });
    }
  },

  addReport: async (formData: ReportFormData) => {
    set({ isLoading: true, error: null });

    const report: Report = {
      country: formData.country,
      datetime: combineDateTime(formData.date, formData.time),
      content: formData.content,
    };

    try {
      const response = await fetch('/api/reports', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(report),
      });

      if (!response.ok) {
        throw new Error('Erreur lors de l\'ajout du rapport');
      }

      const savedReport = await response.json();
      set((state) => ({
        reports: [...state.reports, savedReport],
        isLoading: false,
      }));
    } catch (error) {
      set({
        error: error instanceof Error ? error.message : 'Une erreur est survenue',
        isLoading: false,
      });
      throw error;
    }
  },
}));
