import type { PageServerLoad } from './$types';
import type { DeliveryNote, ReturnGroup } from '$lib/api/types';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	event.depends('app:delivery-note');
	const result = await api(event).get<{ deliveryNote: DeliveryNote; groups: ReturnGroup[] }>(
		`/delivery-notes/${event.params.id}/return-groups`
	);
	return { note: result.deliveryNote, groups: result.groups };
};
