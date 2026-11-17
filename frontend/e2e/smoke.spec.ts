import { expect, test } from '@playwright/test';
import { login, watchConsole } from './helpers';

test('redirects anonymous users to the login page', async ({ page }) => {
	await page.goto('/delivery-notes');
	await expect(page).toHaveURL(/\/login\?next=%2Fdelivery-notes/);
	await expect(page.getByRole('heading', { name: 'Anmelden' })).toBeVisible();
});

test('rejects wrong credentials with a clear message', async ({ page }) => {
	await page.goto('/login');
	await page.getByLabel('E-Mail-Adresse').fill('nobody@example.org');
	await page.getByLabel('Passwort').fill('wrong password');
	await page.getByRole('button', { name: 'Anmelden' }).click();
	await expect(page.getByRole('alert')).toContainText(/falsch|Zu viele/);
});

test('dashboard and main navigation work without CSP violations', async ({ page }) => {
	const problems = watchConsole(page);
	await login(page);
	await expect(page.getByRole('heading', { level: 1 })).toContainText(/Guten|Hallo/);

	for (const [path, heading] of [
		['/delivery-notes', 'Lieferscheine'],
		['/customers', 'Kunden'],
		['/products', 'Artikel'],
		['/order-presets', 'Bestellvorlagen'],
		['/settings', 'Einstellungen']
	] as const) {
		await page.goto(path);
		await expect(page.getByRole('heading', { level: 1, name: heading })).toBeVisible();
	}
	await page.goto('/time-tracking');
	await expect(page.getByRole('heading', { level: 1, name: 'Zeiterfassung' })).toBeVisible();

	expect(problems).toEqual([]);
});

test('logout ends the session', async ({ page }) => {
	await login(page);
	await page.goto('/settings');
	const logout = page.getByRole('button', { name: 'Abmelden' });
	if (!(await logout.isVisible())) await page.getByRole('button', { name: 'Navigation öffnen' }).click();
	await page.getByRole('button', { name: 'Abmelden' }).first().click();
	await expect(page).toHaveURL(/\/login/);
	await page.goto('/customers');
	await expect(page).toHaveURL(/\/login/);
});
