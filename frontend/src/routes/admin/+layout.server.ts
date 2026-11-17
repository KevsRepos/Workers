import { error } from '@sveltejs/kit';
import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = ({ locals }) => {
	if (!locals.user?.isAdmin) error(403, { message: 'Dieser Bereich ist der Verwaltung vorbehalten.' });
};
