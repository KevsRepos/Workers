import { describe, expect, it } from 'vitest';
import { endsNextDay, formToInput, previewHours, previewWarnings, shiftMonth } from './timeTracking';

describe('time tracking', () => {
	it('computes net hours and ArbZG hints', () => {
		const form = { type: 'work' as const, start: '07:00', end: '17:30', breakMinutes: '30', note: '' };
		expect(previewHours(form)).toBe(10);
		expect(previewWarnings(form)).toHaveLength(1);
		expect(previewHours({ ...form, start: '18:00', end: '02:00' })).toBe(7.5);
		expect(endsNextDay({ ...form, start: '18:00', end: '02:00' })).toBe(true);
		expect(previewHours({ ...form, end: '07:00' })).toBeNull();
		expect(previewWarnings({ ...form, end: '14:00', breakMinutes: '0' })).toEqual([
			expect.stringContaining('30 Minuten')
		]);
	});

	it('drops times for absences', () => {
		expect(formToInput(3, { type: 'vacation', start: '08:00', end: '16:00', breakMinutes: '30', note: ' ' })).toEqual({
			day: 3,
			type: 'vacation',
			start: null,
			end: null,
			breakMinutes: null,
			note: null
		});
	});

	it('navigates months across years', () => {
		expect(shiftMonth(2026, 1, -1)).toEqual({ year: 2025, month: 12 });
		expect(shiftMonth(2026, 12, 1)).toEqual({ year: 2027, month: 1 });
	});
});
