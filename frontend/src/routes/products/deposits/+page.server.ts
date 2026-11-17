import type { PageServerLoad } from './$types';
import type { Deposit } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:deposits');
	return { deposits: await api(event).get<Deposit[]>('/deposits') };
};
