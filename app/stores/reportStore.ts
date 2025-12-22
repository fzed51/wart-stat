import { create } from 'zustand';

export type Country = 'US' | 'GER' | 'URRS' | 'UK' | 'JAP' | 'CH' | 'IT' | 'FR' | 'SU' | 'IS';

export interface Report {
  id?: number;
  country: Country;
  datetime: string; // ISO format
  content: string;
}

export interface ReportFormData {
  country: Country;
  date: string;
  time: string;
  content: string;
}

interface ReportState {
  reports: Report[];
  isLoading: boolean;
  error: string | null;
  addReport: (formData: ReportFormData) => Promise<void>;
}

const combineDateTime = (date: string, time: string): string => {
  return new Date(`${date}T${time}`).toISOString();
};

export const useReportStore = create<ReportState>((set) => ({
  reports: [],
  isLoading: false,
  error: null,

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
