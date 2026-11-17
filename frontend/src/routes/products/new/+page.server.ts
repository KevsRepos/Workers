import { error } from '@sveltejs/kit';
import type { PageServerLoad } from './$types';
import type { Deposit } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	if (!event.locals.user?.isAdmin) error(403, { message: 'Artikel können nur von der Verwaltung angelegt werden.' });
	return { deposits: await api(event).get<Deposit[]>('/deposits') };
};
