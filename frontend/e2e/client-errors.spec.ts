import { expect, test } from '@playwright/test';
import { login } from './helpers';

test('browser errors are reported to the server log', async ({ page }) => {
	await login(page);
	const report = page.waitForResponse((r) => r.url().endsWith('/client-errors') && r.request().method() === 'POST');
	await page.evaluate(() => {
		setTimeout(() => {
			throw new Error('e2e boom for max@example.org');
		});
	});
	const response = await report;
	expect(response.status()).toBe(204);
	const body = JSON.parse(response.request().postData() ?? '{}');
	expect(body.kind).toBe('error');
	expect(body.reference).toMatch(/^[0-9a-f-]{36}$/);
});

test('error reports from foreign origins are rejected', async ({ request }) => {
	const response = await request.post('/client-errors', {
		headers: { Origin: 'https://evil.example', 'Content-Type': 'application/json' },
		data: { reference: '1234abcd-0000-4000-8000-000000000000', kind: 'error', message: 'x' }
	});
	expect(response.status()).toBe(403);
});
