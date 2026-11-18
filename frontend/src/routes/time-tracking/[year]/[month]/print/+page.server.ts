import type { PageServerLoad } from './$types';
import type { TimeSheetMonth } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => ({
	sheet: await api(event).get<TimeSheetMonth>(
		`/time-sheets/${event.params.year}/${event.params.month}${query({ accountId: event.url.searchParams.get('accountId') })}`
	)
});
