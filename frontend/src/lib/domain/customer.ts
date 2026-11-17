import type { AddressInput, CustomerInput } from '$lib/api/types';

const isEmptyAddress = (a: AddressInput): boolean =>
	[a.street, a.houseNumber, a.postalCode, a.city].every((v) => !v || v.trim() === '');

/**
 * An untouched address form means "no address" (e.g. pickup customers).
 */
export const withoutEmptyAddresses = (input: CustomerInput): CustomerInput => ({
	...input,
	addresses: (input.addresses ?? []).filter((a) => !isEmptyAddress(a))
});
