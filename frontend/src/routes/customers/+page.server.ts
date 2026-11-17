import type { PageServerLoad } from './$types';
import type { CustomerSummary, Page } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const params = event.url.searchParams;
	return {
		customers: await api(event).get<Page<CustomerSummary>>(
			`/customers${query({ q: params.get('q'), page: params.get('page') ?? 1, limit: 25 })}`
		)
	};
};
