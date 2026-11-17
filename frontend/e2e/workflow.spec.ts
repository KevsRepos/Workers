import { expect, test } from '@playwright/test';
import { login, unique, watchConsole } from './helpers';

test.describe.configure({ mode: 'serial' });

test('master data, customer, delivery note, return and print', async ({ page }) => {
	const problems = watchConsole(page);
	await login(page);

	const productName = unique('E2E Pils 20x0,5l');
	await page.goto('/products/new');
	await page.getByLabel('Bezeichnung').fill(productName);
	await page.getByLabel('Verkaufspreis (€)').fill('18,99');
	await page.getByLabel('Einheiten pro Kiste').fill('20');
	await page.getByRole('button', { name: 'Speichern' }).click();
	await expect(page).toHaveURL(/\/products$/);
	await expect(page.getByText('gespeichert')).toBeVisible();

	const customerName = unique('E2E Sportverein');
	await page.goto('/customers/new');
	await page.getByText('Firma / Verein').click();
	await page.getByLabel('Firmenname').fill(customerName);
	await page.getByLabel('Straße').fill('Am Sportplatz');
	await page.getByLabel('Nr.').fill('1');
	await page.getByLabel('PLZ').fill('12345');
	await page.getByLabel('Ort').fill('Musterstadt');
	await page.getByRole('button', { name: 'Kunde anlegen' }).click();
	await expect(page.getByRole('heading', { level: 1, name: customerName })).toBeVisible();

	await page.getByRole('link', { name: 'Lieferschein', exact: true }).click();
	await expect(page.getByRole('heading', { name: 'Neuer Lieferschein' })).toBeVisible();
	await expect(page.getByText(customerName, { exact: true })).toBeVisible();

	const search = page.getByRole('combobox', { name: 'Artikel hinzufügen' });
	await search.fill(productName);
	await page.getByRole('option', { name: productName }).click();
	await page.getByLabel(`Menge für ${productName}`).fill('12');
	await page.getByLabel('Hinweis für den Kunden').fill('Bitte am Vereinsheim abladen.');
	await page.getByRole('button', { name: 'Lieferschein anlegen' }).click();

	await expect(page.getByText(/Lieferschein LS-\d{4}-\d{5}/)).toBeVisible();
	await expect(page.getByRole('cell', { name: productName })).toBeVisible();

	await page.getByRole('button', { name: 'Geliefert' }).click();
	await expect(page.getByText('Status: Geliefert')).toBeVisible();

	await page.getByRole('link', { name: 'Rückgabe', exact: true }).click();
	await page.getByLabel('Kisten zurück').or(page.getByLabel('Zurück gesamt')).first().fill('11');
	await page.getByRole('button', { name: 'Rückgabe speichern' }).click();
	await expect(page.getByText('Rückgabe gespeichert.')).toBeVisible();
	await expect(page).toHaveURL(/\/delivery-notes\/[0-9a-f-]{36}$/);
	await expect(page.getByRole('button', { name: 'Zurückgegeben', pressed: true })).toBeVisible();
	await expect(page.getByRole('columnheader', { name: 'Zurück' })).toBeVisible();

	await page.goto(page.url() + '/print?copy=return');
	await expect(page.getByRole('heading', { name: 'Rückgabeschein' })).toBeVisible();

	expect(problems).toEqual([]);
});

test('time tracking entry with validation feedback', async ({ page }) => {
	await login(page);
	await page.goto('/time-tracking');
	const todayButton = page.getByRole('button', { name: /Heute (erfassen|bearbeiten)/ });
	await todayButton.click();
	const dialog = page.getByRole('dialog');
	await dialog.getByText('Arbeit', { exact: true }).click();
	await dialog.getByLabel('Beginn').fill('00:00');
	await dialog.getByLabel('Ende').fill('00:30');
	await dialog.getByLabel('Pause (Min.)').fill('0');
	await dialog.getByRole('button', { name: 'Speichern' }).click();
	await expect(page.getByText('Eintrag gespeichert.')).toBeVisible();
	await expect(page.getByText('0,5 h').first()).toBeVisible();
});

test('admin can review and verify the audit log', async ({ page }) => {
	await login(page);
	await page.goto('/admin/audit-log?verify=1');
	await expect(page.getByText('Integrität bestätigt')).toBeVisible();
	await expect(page.getByText('Angelegt').first()).toBeVisible();
});
