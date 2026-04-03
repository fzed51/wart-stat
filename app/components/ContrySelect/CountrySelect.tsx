import { COUNTRIES, type Country } from '../../constants/countries';
import './CountrySelect.css';

interface CountrySelectProps {
  value: Country;
  onChange: (country: Country) => void;
}

export function CountrySelect({ value, onChange }: CountrySelectProps) {
  const selectedCountry = COUNTRIES.find(c => c.value === value);

  return (
    <div className="form-group">
      <label htmlFor="country">Pays</label>
      <div className="select-wrapper">
        {selectedCountry && (
          <img
            src={selectedCountry.flag}
            alt={selectedCountry.label}
            className="select-flag-icon"
          />
        )}
        <select
          id="country"
          className="select-with-flag"
          value={value}
          onChange={(e) => onChange(e.target.value as Country)}
          required
        >
          {COUNTRIES.map((c) => (
            <option key={c.value} value={c.value}>
              {c.label}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}
