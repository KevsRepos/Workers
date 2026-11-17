import type { PageServerLoad } from './$types';
import type { DeliveryNoteSummary, Page } from '$lib/api/types';
import { api } from '$lib/server/backend';
import { query } from '$lib/api/client';

export const load: PageServerLoad = async (event) => {
	const params = event.url.searchParams;
	const status = params.get('status') ?? '1';
	const filters = {
		status: status === 'all' ? null : status,
		q: params.get('q'),
		from: params.get('from'),
		to: params.get('to'),
		page: params.get('page') ?? '1',
		limit: 25
	};

	const client = api(event);
	const [notes, counts] = await Promise.all([
		client.get<Page<DeliveryNoteSummary>>(`/delivery-notes${query(filters)}`),
		client.get<Record<string, number>>('/delivery-notes/status-counts')
	]);

	return { notes, counts, status, from: filters.from ?? '', to: filters.to ?? '' };
};
