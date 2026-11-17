import { describe, expect, it } from 'vitest';
import { DeliveryNoteStatus, type DeliveryNote } from '$lib/api/types';
import { crateDepositCents, draftFromNote, draftToInput, emptyDraft, statusInfo, validateDraft } from './deliveryNote';

describe('delivery note draft', () => {
	it('requires customer, address for deliveries and at least one line', () => {
		const errors = validateDraft(emptyDraft('2026-07-01'));
		expect(Object.keys(errors).sort()).toEqual(['customerId', 'lines', 'shippingAddressId']);
	});

	it('validates the pickup date', () => {
		const draft = { ...emptyDraft('2026-07-10'), pickupDate: '2026-07-01', delivery: false, customerId: 'c' };
		draft.lines = [{ key: 'k', id: null, productId: 'p', productName: 'P', quantity: 1, hasReturn: false }];
		expect(validateDraft(draft)).toEqual({ pickupDate: expect.any(String) });
	});

	it('maps a draft to the API input', () => {
		const draft = {
			...emptyDraft('2026-07-10'),
			customerId: 'c',
			shortDescription: '  ',
			assignment: ' Tom ',
			delivery: false
		};
		draft.lines = [
			{ key: 'a', id: 'l1', productId: 'p1', productName: 'A', quantity: 2, hasReturn: false },
			{ key: 'b', id: null, productId: 'p2', productName: 'B', quantity: 0, hasReturn: false }
		];
		const input = draftToInput(draft);
		expect(input.shortDescription).toBeNull();
		expect(input.assignment).toBe('Tom');
		expect(input.pickupDate).toBeNull();
		expect(input.lines).toEqual([
			{ id: 'l1', productId: 'p1', quantity: 2 },
			{ id: null, productId: 'p2', quantity: null }
		]);
	});

	it('round-trips an existing note', () => {
		const note = {
			customer: { id: 'c', displayName: 'ACME' },
			deliveryDate: '2026-07-10',
			pickupDate: null,
			delivery: true,
			shortDescription: null,
			privateDescription: 'x',
			assignment: null,
			shippingAddress: { id: 'a' },
			billingAddress: null,
			adultGuests: 3,
			childGuests: null,
			callBefore: true,
			lines: [{ id: 'l', productId: 'p', productName: 'Bier', quantity: 5, returnEntry: { id: 'r' } }]
		} as unknown as DeliveryNote;
		const draft = draftFromNote(note);
		expect(draft.lines[0]).toMatchObject({ id: 'l', hasReturn: true });
		expect(draftToInput(draft).shippingAddressId).toBe('a');
	});

	it('computes crate deposits', () => {
		expect(crateDepositCents({ singleAmount: 8, crateAmount: 150 }, 20)).toBe(310);
		expect(crateDepositCents(null, 20)).toBeNull();
		expect(statusInfo(DeliveryNoteStatus.Canceled).label).toBe('Storniert');
	});
});
