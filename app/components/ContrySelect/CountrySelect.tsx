import type { Country } from '../../stores/reportStore';
import './CountrySelect.css';

// Import des drapeaux
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

const COUNTRIES: { value: Country; label: string; flag: string }[] = [
  { value: 'US', label: 'États-Unis', flag: unitedStatesFlag },
  { value: 'GER', label: 'Allemagne', flag: germanyFlag },
  { value: 'URRS', label: 'URSS', flag: russiaFlag },
  { value: 'UK', label: 'Royaume-Uni', flag: unitedKingdomFlag },
  { value: 'JAP', label: 'Japon', flag: japanFlag },
  { value: 'CH', label: 'Chine', flag: chinaFlag },
  { value: 'IT', label: 'Italie', flag: italiaFlag },
  { value: 'FR', label: 'France', flag: franceFlag },
  { value: 'SU', label: 'Suède', flag: swedenFlag },
  { value: 'IS', label: 'Israël', flag: israelFlag },
];

interface CountrySelectProps {
  value: Country;
  onChange: (country: Country) => void;
}

export function CountrySelect({ value, onChange }: CountrySelectProps) {
  return (
    <div className="form-group">
      <label htmlFor="country">Pays</label>
      <div className="select-wrapper" style={{ '--flag-url': `url(${COUNTRIES.find(c => c.value === value)?.flag})` } as React.CSSProperties}>
        <select
          id="country"
          className="select-with-flag"
          value={value}
          onChange={(e) => onChange(e.target.value as Country)}
          required
        >
          {COUNTRIES.map((c) => (
            <option key={c.value} value={c.value} data-flag={c.flag}>
              {c.label}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}
