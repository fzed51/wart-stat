import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { Country } from './reportStore';

interface PreferencesState {
  lastCountry: Country | null;
  updateLastCountry: (country: Country) => void;
}

export const usePreferencesStore = create<PreferencesState>()(
  persist(
    (set) => ({
      lastCountry: null,
      updateLastCountry: (country: Country) => {
        set({ lastCountry: country });
      },
    }),
    {
      name: 'wart-stat-preferences',
    }
  )
);
