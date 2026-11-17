import type { TimeEntry, TimeEntryInput, TimeEntryType } from '$lib/api/types';

export const ENTRY_TYPES: Array<{
	value: TimeEntryType;
	label: string;
	tone: 'brand' | 'info' | 'warning' | 'neutral';
}> = [
	{ value: 'work', label: 'Arbeit', tone: 'brand' },
	{ value: 'vacation', label: 'Urlaub', tone: 'info' },
	{ value: 'sick_leave', label: 'Krank', tone: 'warning' },
	{ value: 'holiday', label: 'Feiertag', tone: 'neutral' }
];

export const typeInfo = (type: TimeEntryType) => ENTRY_TYPES.find((t) => t.value === type) ?? ENTRY_TYPES[0]!;

export interface EntryForm {
	type: TimeEntryType;
	start: string;
	end: string;
	breakMinutes: string;
	note: string;
}

export const formFromEntry = (entry: TimeEntry | null): EntryForm => ({
	type: entry?.type ?? 'work',
	start: entry?.start ?? '',
	end: entry?.end ?? '',
	breakMinutes: entry?.breakMinutes !== null && entry?.breakMinutes !== undefined ? String(entry.breakMinutes) : '',
	note: entry?.note ?? ''
});

export const formToInput = (day: number, form: EntryForm): TimeEntryInput => ({
	day,
	type: form.type,
	start: form.type === 'work' ? form.start || null : null,
	end: form.type === 'work' ? form.end || null : null,
	breakMinutes: form.type === 'work' ? (form.breakMinutes === '' ? 0 : Number(form.breakMinutes)) : null,
	note: form.note.trim() === '' ? null : form.note.trim()
});

const minutes = (time: string): number | null => {
	const match = /^(\d{2}):(\d{2})$/.exec(time);
	return match ? Number(match[1]) * 60 + Number(match[2]) : null;
};

/**
 * Live preview of net working hours while typing.
 */
export const previewHours = (form: EntryForm): number | null => {
	if (form.type !== 'work') return null;
	const start = minutes(form.start);
	const end = minutes(form.end);
	if (start === null || end === null || end === start) return null;
	// end before start = shift ends on the following day
	const span = end > start ? end - start : end + 24 * 60 - start;
	return Math.max(0, (span - Number(form.breakMinutes || 0)) / 60);
};

export const endsNextDay = (form: EntryForm): boolean => {
	const start = minutes(form.start);
	const end = minutes(form.end);
	return form.type === 'work' && start !== null && end !== null && end < start;
};

/**
 * Same ArbZG hints as the backend, shown before saving.
 */
export const previewWarnings = (form: EntryForm): string[] => {
	const hours = previewHours(form);
	if (hours === null) return [];
	const pause = Number(form.breakMinutes || 0);
	const warnings: string[] = [];
	if (hours > 16) warnings.push('Mehr als 16 Stunden – bitte Beginn und Ende prüfen.');
	else if (hours > 10) warnings.push('Mehr als 10 Stunden Arbeitszeit (§ 3 ArbZG).');
	if (hours > 9 && pause < 45)
		warnings.push('Bei mehr als 9 Stunden sind mindestens 45 Minuten Pause vorgeschrieben (§ 4 ArbZG).');
	else if (hours > 6 && pause < 30)
		warnings.push('Bei mehr als 6 Stunden sind mindestens 30 Minuten Pause vorgeschrieben (§ 4 ArbZG).');
	return warnings;
};

export const shiftMonth = (year: number, month: number, delta: number): { year: number; month: number } => {
	const index = year * 12 + (month - 1) + delta;
	return { year: Math.floor(index / 12), month: (index % 12) + 1 };
};
