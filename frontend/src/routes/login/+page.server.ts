import { fail, redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import type { TokenPair } from '$lib/api/types';
import { backendFetch } from '$lib/server/backend';
import { safeRedirectTarget, storeTokens } from '$lib/server/session';

export const load: PageServerLoad = ({ url }) => ({
	expired: url.searchParams.has('expired'),
	next: safeRedirectTarget(url.searchParams.get('next'))
});

export const actions: Actions = {
	default: async (event) => {
		const form = await event.request.formData();
		const email = String(form.get('email') ?? '').trim();
		const password = String(form.get('password') ?? '');
		const next = safeRedirectTarget(String(form.get('next') ?? '/'));

		if (!email || !password) {
			return fail(400, { email, error: 'Bitte E-Mail-Adresse und Passwort eingeben.' });
		}

		const response = await backendFetch(event, '/auth/login', {
			method: 'POST',
			body: { email, password },
			token: null
		});

		if (response.status === 429) {
			return fail(429, { email, error: 'Zu viele fehlgeschlagene Anmeldeversuche. Bitte warte 15 Minuten.' });
		}
		if (response.status === 401) {
			return fail(401, { email, error: 'E-Mail-Adresse oder Passwort ist falsch, oder das Konto ist deaktiviert.' });
		}
		if (!response.ok) {
			return fail(503, { email, error: 'Anmeldung derzeit nicht möglich. Bitte später erneut versuchen.' });
		}

		storeTokens(event.cookies, (await response.json()) as TokenPair);
		redirect(303, next);
	}
};
