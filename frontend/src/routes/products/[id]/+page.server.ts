import type { PageServerLoad } from './$types';
import type { Deposit, Product } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const client = api(event);
	const [product, deposits] = await Promise.all([
		client.get<Product>(`/products/${event.params.id}`),
		client.get<Deposit[]>('/deposits')
	]);
	return { product, deposits };
};
