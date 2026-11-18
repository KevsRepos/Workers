import { describe, expect, it } from 'vitest';
import { RateLimiter, sanitizeReport, scrub } from './clientErrors';

const valid = {
	reference: '1234abcd-0000-4000-8000-000000000000',
	kind: 'error',
	message: 'TypeError: x is undefined',
	path: '/customers/1?q=max',
	stack: 'at foo (app.js:1:2)'
};

describe('client error reports', () => {
	it('accepts and minimises valid reports', () => {
		expect(sanitizeReport(valid)).toEqual({
			reference: valid.reference,
			kind: 'error',
			message: valid.message,
			stack: valid.stack,
			path: '/customers/1',
			status: null,
			release: null
		});
	});

	it('rejects malformed reports', () => {
		expect(sanitizeReport(null)).toBeNull();
		expect(sanitizeReport({ ...valid, reference: '<script>' })).toBeNull();
		expect(sanitizeReport({ ...valid, kind: 'other' })).toBeNull();
		expect(sanitizeReport({ ...valid, message: '' })).toBeNull();
		expect(sanitizeReport({ ...valid, path: 'https://evil.example' })?.path).toBe('/');
	});

	it('removes personal data and secrets', () => {
		expect(scrub('Fehler für max@example.org')).toBe('Fehler für [email]');
		expect(scrub('auth eyJhbGciOi.eyJzdWIiOi.c2lnbmF0dXJl')).toBe('auth [token]');
		expect(scrub('password=geheim123 rest')).toBe('password [redacted] rest');
		expect(scrub('see https://x.example/a/b?email=x&q=1 now')).toBe('see https://x.example/a/b now');
		expect(sanitizeReport({ ...valid, message: 'x'.repeat(2000) })?.message).toHaveLength(500);
	});

	it('limits reports per client', () => {
		let now = 0;
		const limiter = new RateLimiter(2, 1000, () => now);
		expect([limiter.allow('a'), limiter.allow('a'), limiter.allow('a'), limiter.allow('b')]).toEqual([
			true,
			true,
			false,
			true
		]);
		now = 1000;
		expect(limiter.allow('a')).toBe(true);
	});
});
