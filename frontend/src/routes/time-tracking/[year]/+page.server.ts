import type { PageServerLoad } from './$types';
import type { MonthSummary } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const accountId = event.url.searchParams.get('accountId');
	const client = api(event);
	const [months, years] = await Promise.all([
		client.get<MonthSummary[]>(`/time-sheets/${event.params.year}${query({ accountId })}`),
		client.get<number[]>(`/time-sheets/years${query({ accountId })}`)
	]);
	return { year: Number(event.params.year), months, years, accountId };
};
