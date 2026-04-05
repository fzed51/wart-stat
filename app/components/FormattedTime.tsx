interface FormattedTimeProps {
    /** Durée en secondes (optionnel si isoString est fourni) */
    seconds?: number;
    /** Date ISO string (optionnel si seconds est fourni) */
    isoString?: string;
    /** Titre au survol (optionnel) */
    title?: string;
    /** Classe CSS personnalisée (optionnel) */
    className?: string;
}

/**
 * Formate des secondes en format lisible (HH:MM:SS ou MM:SS)
 * @param seconds - Nombre de secondes à formater
 * @returns String formaté
 */
const formatDuration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    return `${minutes}:${String(secs).padStart(2, '0')}`;
};

/**
 * Formate une date ISO string en format HH:MM (français)
 * @param isoString - Date au format ISO string
 * @returns String au format HH:MM
 */
const formatTimeFromISO = (isoString: string): string => {
    const date = new Date(isoString);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

/**
 * Convertit des secondes en format ISO 8601 pour l'attribut dateTime
 * @param seconds - Nombre de secondes
 * @returns String au format PT00H00M00S
 */
const secondsToISO8601Duration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    return `PT${hours}H${minutes}M${secs}S`;
};

/**
 * Composant qui affiche un temps formaté avec l'élément HTML <time>
 * 
 * Accepte soit une durée en secondes, soit une date ISO string
 * 
 * @example
 * // Durée: affiche "02:45"
 * <FormattedTime seconds={165} />
 * 
 * @example
 * // Date: affiche "14:30"
 * <FormattedTime isoString="2026-04-05T14:30:00Z" />
 * 
 * @example
 * // Durée avec titre: affiche "1:02:30"
 * <FormattedTime seconds={3750} title="Durée de la mission" className="mission-time" />
 */
export function FormattedTime({ seconds, isoString, title, className }: FormattedTimeProps) {
    // Valider que l'un des deux paramètres est fourni
    if (seconds === undefined && !isoString) {
        console.warn('FormattedTime: Aucun temps fourni (seconds ou isoString).');
        return <time className={className}>--:--</time>;
    }

    if (seconds !== undefined && isoString) {
        console.warn('FormattedTime: Les deux props "seconds" et "isoString" sont fournies. Seule "seconds" sera utilisée.');
    }

    // Format pour durée en secondes
    if (seconds !== undefined) {
        const formattedDisplay = formatDuration(seconds);
        const isoFormat = secondsToISO8601Duration(seconds);

        return (
            <time
                dateTime={isoFormat}
                title={title}
                className={className}
            >
                {formattedDisplay}
            </time>
        );
    }

    // Format pour date ISO
    if (isoString) {
        const formattedDisplay = formatTimeFromISO(isoString);

        return (
            <time
                dateTime={isoString}
                title={title || new Date(isoString).toLocaleString('fr-FR')}
                className={className}
            >
                {formattedDisplay}
            </time>
        );
    }

    return <time className={className}>--:--</time>;
}

export default FormattedTime;
