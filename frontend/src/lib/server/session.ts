import type { Cookies, RequestEvent } from '@sveltejs/kit';
import { env } from '$env/dynamic/private';
import type { Role, TokenPair } from '$lib/api/types';
import type { SessionUser } from '$lib/session';

export type { SessionUser };

const secure = (): boolean => env.COOKIE_SECURE !== 'false';

/** __Host- prefixed cookies are bound to the exact origin, HTTPS and path "/". */
export const ACCESS_COOKIE = (): string => (secure() ? '__Host-workers_at' : 'workers_at');
export const REFRESH_COOKIE = (): string => (secure() ? '__Host-workers_rt' : 'workers_rt');

const cookieOptions = (expires: Date) =>
	({
		path: '/',
		httpOnly: true,
		secure: secure(),
		sameSite: 'lax',
		expires
	}) as const;

export const storeTokens = (cookies: Cookies, pair: TokenPair): void => {
	cookies.set(ACCESS_COOKIE(), pair.accessToken, cookieOptions(new Date(pair.accessTokenExpiresAt)));
	cookies.set(REFRESH_COOKIE(), pair.refreshToken, cookieOptions(new Date(pair.refreshTokenExpiresAt)));
};

export const clearTokens = (cookies: Cookies): void => {
	cookies.delete(ACCESS_COOKIE(), { path: '/', secure: secure() });
	cookies.delete(REFRESH_COOKIE(), { path: '/', secure: secure() });
};

interface JwtClaims {
	exp?: number;
	username?: string;
	email?: string;
	name?: string;
	roles?: string[];
}

/**
 * Reads (does not verify) the access token claims. The token comes from our own HttpOnly cookie
 * and is verified by the backend on every API call; claims are only used for UI decisions.
 */
export const decodeClaims = (token: string): JwtClaims | null => {
	const payload = token.split('.')[1];
	if (!payload) return null;
	try {
		return JSON.parse(Buffer.from(payload, 'base64url').toString('utf8')) as JwtClaims;
	} catch {
		return null;
	}
};

export const isFresh = (claims: JwtClaims | null, skewSeconds = 30): boolean =>
	!!claims?.exp && claims.exp - skewSeconds > Math.floor(Date.now() / 1000);

export const userFromClaims = (claims: JwtClaims): SessionUser | null => {
	if (!claims.username) return null;
	const roles = (claims.roles ?? []).filter((r): r is Role => r === 'ROLE_USER' || r === 'ROLE_ADMIN');
	return {
		id: claims.username,
		email: claims.email ?? '',
		name: claims.name ?? claims.email ?? '',
		roles,
		isAdmin: roles.includes('ROLE_ADMIN')
	};
};

/**
 * Only allow redirects to local paths (prevents open redirects via ?next=).
 */
export const safeRedirectTarget = (target: string | null | undefined, fallback = '/'): string => {
	if (!target || !target.startsWith('/') || target.startsWith('//') || target.startsWith('/\\')) {
		return fallback;
	}
	return target;
};

export const clientAddress = (event: RequestEvent): string | undefined => {
	try {
		return event.getClientAddress();
	} catch {
		return undefined;
	}
};
