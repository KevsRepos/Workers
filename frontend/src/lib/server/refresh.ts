import type { RequestEvent } from '@sveltejs/kit';
import type { TokenPair } from '$lib/api/types';
import { backendFetch } from './backend';

/**
 * Refresh tokens rotate on every use and reuse is treated as theft by the backend. Parallel
 * requests of the same browser (page + data requests) must therefore share one refresh call.
 * Results are cached briefly per old token so late parallel requests get the same new pair.
 */
const inFlight = new Map<string, Promise<TokenPair | null>>();
const recent = new Map<string, { pair: TokenPair | null; until: number }>();
const REUSE_WINDOW_MS = 20_000;

export const refreshSession = (event: RequestEvent, refreshToken: string): Promise<TokenPair | null> => {
	const now = Date.now();
	for (const [key, value] of recent) {
		if (value.until < now) recent.delete(key);
	}

	const cached = recent.get(refreshToken);
	if (cached) return Promise.resolve(cached.pair);

	let promise = inFlight.get(refreshToken);
	if (!promise) {
		promise = (async () => {
			const response = await backendFetch(event, '/auth/refresh', {
				method: 'POST',
				body: { refreshToken },
				token: null
			});
			const pair = response.ok ? ((await response.json()) as TokenPair) : null;
			recent.set(refreshToken, { pair, until: Date.now() + REUSE_WINDOW_MS });
			return pair;
		})().finally(() => inFlight.delete(refreshToken));
		inFlight.set(refreshToken, promise);
	}

	return promise;
};
