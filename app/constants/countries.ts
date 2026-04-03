import unitedStatesFlag from '../assets/united-states.svg';
import germanyFlag from '../assets/germany.svg';
import russiaFlag from '../assets/russia.svg';
import unitedKingdomFlag from '../assets/united-kingdom.svg';
import japanFlag from '../assets/japan.svg';
import chinaFlag from '../assets/china.svg';
import italiaFlag from '../assets/italia.svg';
import franceFlag from '../assets/france.svg';
import swedenFlag from '../assets/sweden.svg';
import israelFlag from '../assets/israel.svg';

export type Country = 'US' | 'GER' | 'URRS' | 'UK' | 'JAP' | 'CH' | 'IT' | 'FR' | 'SU' | 'IL';

export interface CountryInfo {
    value: Country;
    label: string;
    flag: string;
    emoji: string;
}

export const COUNTRIES: CountryInfo[] = [
    { value: 'US', label: 'États-Unis', flag: unitedStatesFlag, emoji: "" },
    { value: 'GER', label: 'Allemagne', flag: germanyFlag, emoji: "" },
    { value: 'URRS', label: 'URSS', flag: russiaFlag, emoji: "" },
    { value: 'UK', label: 'Royaume-Uni', flag: unitedKingdomFlag, emoji: "" },
    { value: 'JAP', label: 'Japon', flag: japanFlag, emoji: "" },
    { value: 'CH', label: 'Chine', flag: chinaFlag, emoji: "" },
    { value: 'IT', label: 'Italie', flag: italiaFlag, emoji: "" },
    { value: 'FR', label: 'France', flag: franceFlag, emoji: "" },
    { value: 'SU', label: 'Suède', flag: swedenFlag, emoji: "" },
    { value: 'IL', label: 'Israël', flag: israelFlag, emoji: "" },
];

/**
 * Récupère les informations complètes d'un pays
 */
export function getCountryInfo(countryCode: Country): CountryInfo | undefined {
    return COUNTRIES.find(c => c.value === countryCode);
}

/**
 * Récupère le label d'un pays à partir de son code
 */
export function getCountryLabel(countryCode: Country | string): string {
    const countryInfo = getCountryInfo(countryCode as Country);
    return countryInfo?.label || countryCode;
}
