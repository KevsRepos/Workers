import type { PageServerLoad } from './$types';
import type { Customer, DeliveryNoteSummary, Page } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:customer');
	const client = api(event);
	const [customer, notes] = await Promise.all([
		client.get<Customer>(`/customers/${event.params.id}`),
		client.get<Page<DeliveryNoteSummary>>(`/delivery-notes?customerId=${event.params.id}&limit=10`)
	]);
	return { customer, notes };
};
