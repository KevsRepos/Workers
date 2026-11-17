import { expect, type Page } from '@playwright/test';

export const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL ?? 'admin@example.org';
export const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'secret-passphrase-for-admin-1';

export const login = async (page: Page, email = ADMIN_EMAIL, password = ADMIN_PASSWORD) => {
	await page.goto('/login');
	await page.getByLabel('E-Mail-Adresse').fill(email);
	await page.getByLabel('Passwort').fill(password);
	await page.getByRole('button', { name: 'Anmelden' }).click();
	await expect(page).not.toHaveURL(/\/login/);
};

/** Collects CSP violations and uncaught errors reported by the browser. */
export const watchConsole = (page: Page) => {
	const problems: string[] = [];
	page.on('console', (message) => {
		if (message.type() === 'error' && /Content Security Policy|Refused to/i.test(message.text())) problems.push(message.text());
	});
	page.on('pageerror', (error) => problems.push(error.message));
	return problems;
};

export const unique = (prefix: string) => `${prefix} ${Date.now().toString(36)}`;
