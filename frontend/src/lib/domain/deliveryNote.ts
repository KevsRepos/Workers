import { DeliveryNoteStatus, type DeliveryNote, type DeliveryNoteInput } from '$lib/api/types';

export const STATUS_OPTIONS: Array<{
	value: DeliveryNoteStatus;
	label: string;
	tone: 'info' | 'brand' | 'neutral' | 'success' | 'danger';
}> = [
	{ value: DeliveryNoteStatus.Open, label: 'Offen', tone: 'info' },
	{ value: DeliveryNoteStatus.Delivered, label: 'Geliefert', tone: 'brand' },
	{ value: DeliveryNoteStatus.Returned, label: 'Zurückgegeben', tone: 'neutral' },
	{ value: DeliveryNoteStatus.Completed, label: 'Abgeschlossen', tone: 'success' },
	{ value: DeliveryNoteStatus.Canceled, label: 'Storniert', tone: 'danger' }
];

export const statusInfo = (status: DeliveryNoteStatus) =>
	STATUS_OPTIONS.find((s) => s.value === status) ?? STATUS_OPTIONS[0]!;

export interface DraftLine {
	key: string;
	id: string | null;
	productId: string;
	productName: string;
	quantity: number | null;
	hasReturn: boolean;
}

export interface DeliveryNoteDraft {
	customerId: string | null;
	customerName: string;
	deliveryDate: string;
	pickupDate: string;
	delivery: boolean;
	shortDescription: string;
	privateDescription: string;
	assignment: string;
	shippingAddressId: string | null;
	billingAddressId: string | null;
	adultGuests: number | null;
	childGuests: number | null;
	callBefore: boolean;
	lines: DraftLine[];
}

export const emptyDraft = (deliveryDate: string): DeliveryNoteDraft => ({
	customerId: null,
	customerName: '',
	deliveryDate,
	pickupDate: '',
	delivery: true,
	shortDescription: '',
	privateDescription: '',
	assignment: '',
	shippingAddressId: null,
	billingAddressId: null,
	adultGuests: null,
	childGuests: null,
	callBefore: false,
	lines: []
});

let keyCounter = 0;
export const lineKey = (): string => `line-${Date.now().toString(36)}-${(keyCounter++).toString(36)}`;

export const draftFromNote = (note: DeliveryNote): DeliveryNoteDraft => ({
	customerId: note.customer.id,
	customerName: note.customer.displayName,
	deliveryDate: note.deliveryDate,
	pickupDate: note.pickupDate ?? '',
	delivery: note.delivery,
	shortDescription: note.shortDescription ?? '',
	privateDescription: note.privateDescription ?? '',
	assignment: note.assignment ?? '',
	shippingAddressId: note.shippingAddress?.id ?? null,
	billingAddressId: note.billingAddress?.id ?? null,
	adultGuests: note.adultGuests,
	childGuests: note.childGuests,
	callBefore: note.callBefore,
	lines: note.lines.map((line) => ({
		key: line.id,
		id: line.id,
		productId: line.productId,
		productName: line.productName,
		quantity: line.quantity,
		hasReturn: line.returnEntry !== null
	}))
});

const text = (value: string): string | null => (value.trim() === '' ? null : value.trim());

export const draftToInput = (draft: DeliveryNoteDraft): DeliveryNoteInput => ({
	customerId: draft.customerId ?? '',
	deliveryDate: draft.deliveryDate,
	pickupDate: draft.pickupDate === '' ? null : draft.pickupDate,
	delivery: draft.delivery,
	shortDescription: text(draft.shortDescription),
	privateDescription: text(draft.privateDescription),
	assignment: text(draft.assignment),
	shippingAddressId: draft.shippingAddressId,
	billingAddressId: draft.billingAddressId,
	adultGuests: draft.adultGuests,
	childGuests: draft.childGuests,
	callBefore: draft.callBefore,
	lines: draft.lines.map((line) => ({
		id: line.id,
		productId: line.productId,
		quantity: line.quantity && line.quantity > 0 ? line.quantity : null
	}))
});

/**
 * Client-side pre-validation (the backend validates authoritatively).
 */
export const validateDraft = (draft: DeliveryNoteDraft): Record<string, string> => {
	const errors: Record<string, string> = {};
	if (!draft.customerId) errors.customerId = 'Bitte einen Kunden auswählen.';
	if (!draft.deliveryDate) errors.deliveryDate = 'Bitte ein Datum wählen.';
	if (draft.pickupDate && draft.deliveryDate && draft.pickupDate < draft.deliveryDate) {
		errors.pickupDate = 'Das Abholdatum darf nicht vor dem Lieferdatum liegen.';
	}
	if (draft.delivery && !draft.shippingAddressId)
		errors.shippingAddressId = 'Für eine Lieferung wird eine Lieferadresse benötigt.';
	if (draft.lines.length === 0) errors.lines = 'Mindestens einen Artikel hinzufügen.';
	draft.lines.forEach((line, index) => {
		if (line.quantity !== null && line.quantity <= 0)
			errors[`lines[${index}].quantity`] = 'Menge muss größer als 0 sein.';
	});
	return errors;
};

/**
 * Deposit value of a crate including bottles, in cents.
 */
export const crateDepositCents = (
	deposit: { singleAmount: number; crateAmount: number | null } | null,
	quantityInCrate: number | null
): number | null => {
	if (!deposit) return null;
	return (deposit.crateAmount ?? 0) + deposit.singleAmount * (quantityInCrate ?? 0);
};
