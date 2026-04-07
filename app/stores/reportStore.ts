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

export interface ReportUpdateData {
  country?: Country;
  datetime?: string;
}

export interface ReportDetailData {
  report: {
    id: number;
    country: string;
    datetime: string;
    session_id: string;
    content: string;
  };
  mission: {
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
  };
  actions: Array<{
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
  }>;
  bonuses: Array<{
    id: number;
    mission_id: number;
    bonus_name: string;
    timestamp_sec: number;
    sl_awarded: number;
    rp_awarded: number;
  }>;
}

interface ReportState {
  reports: ReportDetail[];
  reportDetail: ReportDetailData | null;
  isLoading: boolean;
  error: string | null;
  fetchReports: () => Promise<void>;
  fetchReportDetail: (reportId: number) => Promise<void>;
  addReport: (formData: ReportFormData) => Promise<void>;
  updateReport: (reportId: number, data: ReportUpdateData) => Promise<void>;
}

const combineDateTime = (date: string, time: string): string => {
  return new Date(`${date}T${time}`).toISOString();
};

export const useReportStore = create<ReportState>((set) => ({
  reports: [],
  reportDetail: null,
  isLoading: false,
  error: null,

  fetchReportDetail: async (reportId: number) => {
    set({ isLoading: true, error: null });

    try {
      const response = await fetch(`/api/reports/${reportId}`);

      if (!response.ok) {
        throw new Error('Rapport non trouvé');
      }

      const data = await response.json();
      set({ reportDetail: data, isLoading: false });
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erreur lors du chargement';
      set({
        error: errorMessage,
        isLoading: false,
      });
      throw error;
    }
  },

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

  updateReport: async (reportId: number, data: ReportUpdateData) => {
    set({ isLoading: true, error: null });

    try {
      const response = await fetch(`/api/reports/${reportId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        throw new Error('Erreur lors de la mise à jour du rapport');
      }

      const updatedReport = await response.json();
      set((state) => ({
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
