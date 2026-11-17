import { describe, expect, it } from 'vitest';
import { decodeClaims, isFresh, safeRedirectTarget, userFromClaims } from './session';

const token = (payload: object) => `h.${Buffer.from(JSON.stringify(payload)).toString('base64url')}.s`;

describe('session helpers', () => {
	it('decodes claims and checks freshness', () => {
		const exp = Math.floor(Date.now() / 1000) + 600;
		const claims = decodeClaims(
			token({ exp, username: 'id-1', email: 'a@b.de', name: 'A B', roles: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_HACK'] })
		);
		expect(isFresh(claims)).toBe(true);
		expect(userFromClaims(claims!)).toEqual({
			id: 'id-1',
			email: 'a@b.de',
			name: 'A B',
			roles: ['ROLE_USER', 'ROLE_ADMIN'],
			isAdmin: true
		});
		expect(isFresh(decodeClaims(token({ exp: Math.floor(Date.now() / 1000) + 10 })))).toBe(false);
		expect(decodeClaims('garbage')).toBeNull();
	});

	it('only redirects to local paths', () => {
		expect(safeRedirectTarget('/delivery-notes?status=1')).toBe('/delivery-notes?status=1');
		expect(safeRedirectTarget('//evil.example')).toBe('/');
		expect(safeRedirectTarget('https://evil.example')).toBe('/');
		expect(safeRedirectTarget('/\\evil.example')).toBe('/');
		expect(safeRedirectTarget(null)).toBe('/');
	});
});
