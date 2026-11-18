import type { PageServerLoad } from './$types';
import type { ProductUnion } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:unions');
	return { unions: await api(event).get<ProductUnion[]>('/product-unions') };
};
