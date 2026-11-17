import type { PageServerLoad } from './$types';
import type { Page, Product } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const params = event.url.searchParams;
	const filter = params.get('filter') ?? 'all';
	return {
		filter,
		products: await api(event).get<Page<Product>>(
			`/products${query({ filter, q: params.get('q'), page: params.get('page') ?? 1, limit: 30 })}`
		)
	};
};
