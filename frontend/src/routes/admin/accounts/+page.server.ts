import type { PageServerLoad } from './$types';
import type { Account } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:accounts');
	return { accounts: await api(event).get<Account[]>('/admin/accounts') };
};
