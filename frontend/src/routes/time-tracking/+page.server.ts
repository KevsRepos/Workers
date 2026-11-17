import { redirect } from '@sveltejs/kit';
import type { PageServerLoad } from './$types';
import { todayIso } from '$lib/utils/format';

export const load: PageServerLoad = ({ url }) => {
	const [year, month] = todayIso().split('-').map(Number);
	redirect(307, `/time-tracking/${year}/${month}${url.search}`);
};
