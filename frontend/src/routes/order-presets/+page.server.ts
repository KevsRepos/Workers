import type { PageServerLoad } from './$types';
import type { OrderPreset } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:presets');
	return { presets: await api(event).get<OrderPreset[]>('/order-presets') };
};
