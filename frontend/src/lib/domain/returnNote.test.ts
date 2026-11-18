import { describe, expect, it } from 'vitest';
import type { ReturnGroup } from '$lib/api/types';
import { depositRefundCents, rowErrors, rowsFromGroups, rowsToEntries } from './returnNote';

const group = (overrides: Partial<ReturnGroup> = {}): ReturnGroup => ({
	key: 'k',
	name: 'Kasten',
	isUnion: true,
	lineIds: ['l1', 'l2'],
	productNames: ['A', 'B'],
	quantity: 10,
	rentable: false,
	quantityInCrate: 20,
	deposit: { id: 'd', label: null, singleAmount: 8, crateAmount: 150 },
	returnEntry: null,
	...overrides
});

describe('return note', () => {
	it('only submits touched rows', () => {
		const rows = rowsFromGroups([group(), group({ key: 'x', lineIds: ['l3'], deposit: null, quantityInCrate: null })]);
		rows[0]!.returnedTotal = 9;
		rows[1]!.returnedTotalBottles = 4;
		expect(rowsToEntries(rows)).toEqual([
			{
				lineIds: ['l1', 'l2'],
				returnedTotal: 9,
				returnedFull: null,
				returnedTotalBottles: null,
				returnedFullBottles: null
			},
			{
				lineIds: ['l3'],
				returnedTotal: null,
				returnedFull: null,
				returnedTotalBottles: null,
				returnedFullBottles: null
			}
		]);
	});

	it('calculates deposit refunds and validates full counts', () => {
		const [row] = rowsFromGroups([group()]);
		row!.returnedTotal = 2;
		row!.returnedTotalBottles = 3;
		expect(depositRefundCents(row!)).toBe(2 * 310 + 3 * 8);
		row!.returnedFull = 3;
		expect(rowErrors(row!)).not.toBeNull();
	});
});
