import { error } from '@sveltejs/kit';
import type { PageServerLoad } from './$types';
import type { Account, TimeSheetMonth } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const year = Number(event.params.year);
	const month = Number(event.params.month);
	if (!Number.isInteger(year) || !Number.isInteger(month) || month < 1 || month > 12)
		error(404, { message: 'Ungültiger Monat.' });

	event.depends('app:time-sheet');
	const accountId = event.url.searchParams.get('accountId');
	const client = api(event);
	const [sheet, accounts] = await Promise.all([
		client.get<TimeSheetMonth>(`/time-sheets/${year}/${month}${query({ accountId })}`),
		event.locals.user?.isAdmin ? client.get<Account[]>('/admin/accounts') : Promise.resolve([] as Account[])
	]);
	return { sheet, accounts, accountId };
};
