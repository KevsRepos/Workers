import { fail } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import type { ReminderSettings } from '$lib/api/types';
import { ApiError } from '$lib/api/errors';
import { api, backendJson } from '$lib/server/backend';
import { REFRESH_COOKIE } from '$lib/server/session';

export const load: PageServerLoad = async (event) => ({
	reminder: await api(event).get<ReminderSettings>('/time-tracking/reminder')
});

export const actions: Actions = {
	/** Server-side so the current session's refresh token can be kept while all others are revoked. */
	password: async (event) => {
		const form = await event.request.formData();
		const body = {
			currentPassword: String(form.get('currentPassword') ?? ''),
			newPassword: String(form.get('newPassword') ?? ''),
			confirmNewPassword: String(form.get('confirmNewPassword') ?? ''),
			refreshToken: event.cookies.get(REFRESH_COOKIE()) ?? null
		};
		try {
			await backendJson(event, '/auth/change-password', { method: 'POST', body });
			return { passwordChanged: true };
		} catch (e) {
			if (e instanceof ApiError) {
				return fail(e.status, {
					passwordErrors: e.fieldErrors,
					passwordError: e.status >= 500 ? 'Das Passwort konnte nicht geändert werden.' : null
				});
			}
			throw e;
		}
	}
};
