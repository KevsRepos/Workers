import type { HandleClientError } from '@sveltejs/kit';
import { setUnexpectedErrorReporter } from '$lib/api/errors';
import { reportClientError } from '$lib/clientErrors';

/**
 * Unexpected errors during client-side navigation/rendering: report them and show the reference
 * on the error page.
 */
export const handleError: HandleClientError = ({ error, status, message }) => {
	if (status === 404) {
		return { message: 'Seite nicht gefunden.', status };
	}
	const requestId = reportClientError('navigation', error, { status });
	return { message: status >= 500 ? 'Ein unerwarteter Fehler ist aufgetreten.' : message, requestId, status };
};

if (typeof window !== 'undefined') {
	setUnexpectedErrorReporter((error) => reportClientError('error', error));

	window.addEventListener('error', (event) => {
		// Ignore failed resource loads (images etc.); only script errors carry an error object.
		if (event.error) reportClientError('error', event.error);
	});
	window.addEventListener('unhandledrejection', (event) => {
		reportClientError('unhandledrejection', event.reason);
	});
}
