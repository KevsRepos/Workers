import type { ReturnEntryInput, ReturnGroup } from '$lib/api/types';
import { crateDepositCents } from './deliveryNote';

export interface ReturnRow {
	group: ReturnGroup;
	returnedTotal: number | null;
	returnedFull: number | null;
	returnedTotalBottles: number | null;
	returnedFullBottles: number | null;
}

export const rowsFromGroups = (groups: ReturnGroup[]): ReturnRow[] =>
	groups.map((group) => ({
		group,
		returnedTotal: group.returnEntry?.returnedTotal ?? null,
		returnedFull: group.returnEntry?.returnedFull ?? null,
		returnedTotalBottles: group.returnEntry?.returnedTotalBottles ?? null,
		returnedFullBottles: group.returnEntry?.returnedFullBottles ?? null
	}));

export const hasBottles = (group: ReturnGroup): boolean => group.deposit !== null && (group.quantityInCrate ?? 0) > 0;

export const isTouched = (row: ReturnRow): boolean =>
	[row.returnedTotal, row.returnedFull, row.returnedTotalBottles, row.returnedFullBottles].some((v) => v !== null);

export const rowsToEntries = (rows: ReturnRow[]): ReturnEntryInput[] =>
	rows.filter(isTouched).map((row) => ({
		lineIds: row.group.lineIds,
		returnedTotal: row.returnedTotal,
		returnedFull: row.returnedFull,
		returnedTotalBottles: hasBottles(row.group) ? row.returnedTotalBottles : null,
		returnedFullBottles: hasBottles(row.group) ? row.returnedFullBottles : null
	}));

/**
 * Deposit refunded for returned crates and loose bottles, in cents.
 */
export const depositRefundCents = (row: ReturnRow): number => {
	if (!row.group.deposit) return 0;
	const crate = crateDepositCents(row.group.deposit, row.group.quantityInCrate) ?? 0;
	return (row.returnedTotal ?? 0) * crate + (row.returnedTotalBottles ?? 0) * row.group.deposit.singleAmount;
};

export const rowErrors = (row: ReturnRow): string | null => {
	if ((row.returnedFull ?? 0) > (row.returnedTotal ?? 0) && row.returnedFull !== null)
		return 'Volle können nicht mehr sein als zurückgegebene.';
	if ((row.returnedFullBottles ?? 0) > (row.returnedTotalBottles ?? 0) && row.returnedFullBottles !== null)
		return 'Volle Flaschen können nicht mehr sein als zurückgegebene.';
	return null;
};
