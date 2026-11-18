import { describe, expect, it } from 'vitest';
import {
	addDaysIso,
	centsToEuroInput,
	formatCents,
	formatDate,
	nullable,
	numberOrNull,
	parseEuroToCents,
	todayIso
} from './format';

describe('format', () => {
	it('formats ISO dates without timezone shift', () => {
		expect(formatDate('2026-01-01')).toBe('01.01.2026');
		expect(formatDate('2026-12-31')).toBe('31.12.2026');
		expect(formatDate(null)).toBe('–');
	});

	it('formats cents as euro', () => {
		expect(formatCents(1899)).toMatch(/18,99\s€/);
		expect(formatCents(null)).toBe('–');
	});

	it('parses euro input to cents', () => {
		expect(parseEuroToCents('12,50')).toBe(1250);
		expect(parseEuroToCents('12.5')).toBe(1250);
		expect(parseEuroToCents('1.234,56 €')).toBe(123456);
		expect(parseEuroToCents('')).toBeNull();
		expect(parseEuroToCents('abc')).toBeNull();
		expect(centsToEuroInput(1250)).toBe('12,50');
	});

	it('handles dates across month boundaries', () => {
		expect(addDaysIso('2026-01-31', 1)).toBe('2026-02-01');
		expect(addDaysIso('2024-02-28', 1)).toBe('2024-02-29');
		expect(todayIso(new Date('2026-06-30T22:30:00Z'))).toBe('2026-07-01');
	});

	it('normalises optional values', () => {
		expect(nullable('  ')).toBeNull();
		expect(nullable(' a ')).toBe('a');
		expect(numberOrNull('1,5')).toBe(1.5);
		expect(numberOrNull('')).toBeNull();
	});
});
