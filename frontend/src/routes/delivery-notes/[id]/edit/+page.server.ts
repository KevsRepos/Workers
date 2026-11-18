import type { PageServerLoad } from './$types';
import type { Customer, DeliveryNote } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const client = api(event);
	const note = await client.get<DeliveryNote>(`/delivery-notes/${event.params.id}`);
	const customer = await client.get<Customer>(`/customers/${note.customer.id}`);
	return { note, customer };
};
