export const VOLUME_HIGHLIGHTS = {
    '24x0,33l': 'highlight-24033l',
    '20x0,33l': 'highlight-20033l',
    '20x0,5l': 'highlight-2005l',
    '24x0,5l': 'highlight-2405l',
    '11x0,5l': 'highlight-1105l',
    '12x1l': 'highlight-121l',
    '6x1l': 'highlight-61l',
    '28x0,25l': 'highlight-28025l',
    '24x0,25l': 'highlight-24025l',
    '12x0,7l': 'highlight-1207l',
    '30x0,33l': 'highlight-30033l',
    '5l': 'highlight-5l',
    '30l': 'highlight-30l',
    '50l': 'highlight-50l',
};

export const highlightVolumePart = (string: string): { text: string; className?: string }[] => {
    for (const [volume, className] of Object.entries(VOLUME_HIGHLIGHTS)) {
        const startIndex = string.indexOf(volume);

        if (startIndex === -1) continue;

        return [
            { text: string.slice(0, startIndex) },
            { text: volume, className: `volume-highlight ${className}` },
            { text: string.slice(startIndex + volume.length) },
        ];
    }

    return [{ text: string }];
}