import { ApiError, errorFromResponse } from './errors';

/**
 * Browser-side API client. Talks to the same-origin proxy (/api/…), never to the backend directly.
 */
const request = async <T>(method: string, path: string, body?: unknown): Promise<T> => {
	let response: Response;
	try {
		response = await fetch(`/api/${path.replace(/^\/+/, '')}`, {
			method,
			headers:
				body !== undefined
					? { 'Content-Type': 'application/json', Accept: 'application/json' }
					: { Accept: 'application/json' },
			body: body !== undefined ? JSON.stringify(body) : undefined,
			credentials: 'same-origin'
		});
	} catch (cause) {
		throw new ApiError(0, {
			type: 'urn:workers:problem:network',
			title: 'Network error',
			status: 0,
			detail: String(cause)
		});
	}

	if (response.status === 401 && typeof window !== 'undefined') {
		const next = encodeURIComponent(window.location.pathname + window.location.search);
		window.location.assign(`/login?next=${next}&expired=1`);
	}

	if (!response.ok) {
		throw await errorFromResponse(response);
	}

	return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
};

export const api = {
	get: <T>(path: string) => request<T>('GET', path),
	post: <T>(path: string, body?: unknown) => request<T>('POST', path, body ?? {}),
	put: <T>(path: string, body?: unknown) => request<T>('PUT', path, body ?? {}),
	delete: (path: string) => request<void>('DELETE', path)
};

export const query = (params: Record<string, string | number | boolean | null | undefined>): string => {
	const search = new URLSearchParams();
	for (const [key, value] of Object.entries(params)) {
		if (value !== null && value !== undefined && value !== '') search.set(key, String(value));
	}
	const result = search.toString();
	return result ? `?${result}` : '';
};
