import type { PageServerLoad } from './$types';
import type { DeliveryNote, ReturnGroup } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const result = await api(event).get<{ deliveryNote: DeliveryNote; groups: ReturnGroup[] }>(
		`/delivery-notes/${event.params.id}/return-groups`
	);
	const copy = event.url.searchParams.get('copy') === 'return' ? 'return' : 'delivery';
	return { note: result.deliveryNote, groups: result.groups, copy };
};
