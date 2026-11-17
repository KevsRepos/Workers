const dateFormatter = new Intl.DateTimeFormat('de-DE', {
	day: '2-digit',
	month: '2-digit',
	year: 'numeric',
	timeZone: 'Europe/Berlin'
});
const weekdayDateFormatter = new Intl.DateTimeFormat('de-DE', {
	weekday: 'short',
	day: '2-digit',
	month: '2-digit',
	year: 'numeric',
	timeZone: 'Europe/Berlin'
});
const dateTimeFormatter = new Intl.DateTimeFormat('de-DE', {
	dateStyle: 'medium',
	timeStyle: 'short',
	timeZone: 'Europe/Berlin'
});
const currencyFormatter = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });
const numberFormatter = new Intl.NumberFormat('de-DE', { maximumFractionDigits: 2 });

/** Parses "YYYY-MM-DD" as a calendar date (no timezone shift). */
export const parseIsoDate = (value: string): Date => {
	const [y, m, d] = value.slice(0, 10).split('-').map(Number);
	return new Date(Date.UTC(y ?? 1970, (m ?? 1) - 1, d ?? 1, 12));
};

export const formatDate = (value: string | null | undefined): string =>
	value ? dateFormatter.format(parseIsoDate(value)) : '–';

export const formatWeekdayDate = (value: string | null | undefined): string =>
	value ? weekdayDateFormatter.format(parseIsoDate(value)) : '–';

export const formatDateTime = (value: string | null | undefined): string =>
	value ? dateTimeFormatter.format(new Date(value)) : '–';

/** Amounts are stored in cents. */
export const formatCents = (cents: number | null | undefined): string =>
	cents === null || cents === undefined ? '–' : currencyFormatter.format(cents / 100);

export const formatNumber = (value: number | null | undefined): string =>
	value === null || value === undefined ? '–' : numberFormatter.format(value);

export const formatHours = (hours: number): string => `${numberFormatter.format(hours)} h`;

export const MONTHS = [
	'Januar',
	'Februar',
	'März',
	'April',
	'Mai',
	'Juni',
	'Juli',
	'August',
	'September',
	'Oktober',
	'November',
	'Dezember'
];
export const WEEKDAYS_SHORT = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];

export const monthName = (month: number): string => MONTHS[month - 1] ?? String(month);

/** Today as YYYY-MM-DD in Europe/Berlin. */
export const todayIso = (now: Date = new Date()): string =>
	new Intl.DateTimeFormat('en-CA', {
		timeZone: 'Europe/Berlin',
		year: 'numeric',
		month: '2-digit',
		day: '2-digit'
	}).format(now);

export const addDaysIso = (iso: string, days: number): string => {
	const date = parseIsoDate(iso);
	date.setUTCDate(date.getUTCDate() + days);
	return date.toISOString().slice(0, 10);
};

/** "12,50" | "12.5" → 1250 cents; empty → null. */
export const parseEuroToCents = (value: string | number | null | undefined): number | null => {
	if (value === null || value === undefined || value === '') return null;
	const normalized = String(value)
		.trim()
		.replace(/\s|€/g, '')
		.replace(/\.(?=\d{3}(\D|$))/g, '')
		.replace(',', '.');
	const number = Number(normalized);
	return Number.isFinite(number) ? Math.round(number * 100) : null;
};

export const centsToEuroInput = (cents: number | null | undefined): string =>
	cents === null || cents === undefined ? '' : (cents / 100).toFixed(2).replace('.', ',');

/** Turns empty strings into null and trims text. */
export const nullable = (value: string | null | undefined): string | null => {
	const trimmed = value?.trim() ?? '';
	return trimmed === '' ? null : trimmed;
};

export const numberOrNull = (value: string | number | null | undefined): number | null => {
	if (value === null || value === undefined || value === '') return null;
	const number = typeof value === 'number' ? value : Number(String(value).replace(',', '.'));
	return Number.isFinite(number) ? number : null;
};
