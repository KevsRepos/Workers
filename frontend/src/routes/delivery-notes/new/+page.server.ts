import type { PageServerLoad } from './$types';
import type { Customer } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const customerId = event.url.searchParams.get('customerId');
	return {
		customer:
			customerId && /^[0-9a-f-]{36}$/i.test(customerId)
				? await api(event).get<Customer>(`/customers/${customerId}`)
				: null
	};
};
