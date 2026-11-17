import { describe, expect, it } from 'vitest';
import { allowedBackendPath } from './proxy';

describe('proxy allowlist', () => {
	it('allows known API resources', () => {
		expect(allowedBackendPath('customers')).toBe('/customers');
		expect(allowedBackendPath('delivery-notes/123/return-note')).toBe('/delivery-notes/123/return-note');
		expect(allowedBackendPath('admin/accounts')).toBe('/admin/accounts');
	});

	it('rejects auth endpoints and traversal', () => {
		expect(allowedBackendPath('auth/login')).toBeNull();
		expect(allowedBackendPath('auth/refresh')).toBeNull();
		expect(allowedBackendPath('customers/../auth/login')).toBeNull();
		expect(allowedBackendPath('customers/%2e%2e')).toBeNull();
		expect(allowedBackendPath('')).toBeNull();
		expect(allowedBackendPath('health')).toBeNull();
		expect(allowedBackendPath('customersX')).toBeNull();
	});
});
