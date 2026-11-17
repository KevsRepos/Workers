import { redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import { backendFetch } from '$lib/server/backend';
import { REFRESH_COOKIE, clearTokens } from '$lib/server/session';

export const load: PageServerLoad = () => redirect(303, '/');

export const actions: Actions = {
	default: async (event) => {
		const refreshToken = event.cookies.get(REFRESH_COOKIE());
		if (refreshToken) {
			// Revoke the session server-side; failures must not keep the user logged in locally.
			await backendFetch(event, '/auth/logout', { method: 'POST', body: { refreshToken }, token: null }).catch(
				() => undefined
			);
		}
		clearTokens(event.cookies);
		redirect(303, '/login');
	}
};
