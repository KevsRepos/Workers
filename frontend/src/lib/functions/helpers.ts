export const centToEuro = (value: number | null): string => {
    if (typeof value !== 'number') return '';
    return (value / 100).toFixed(2);
}

export const euroToCent = (value: string): number | null => {
    if (!value) return null;
    return Math.round(parseFloat(value) * 100);
}
