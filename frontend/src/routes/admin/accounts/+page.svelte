<script lang="ts">
	import { invalidate } from '$app/navigation';
	import { KeyRound, Pencil, Plus } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Account, Role } from '$lib/api/types';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Checkbox from '$lib/components/ui/Checkbox.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import { toast } from '$lib/stores/toast.svelte';
	import { formatDateTime } from '$lib/utils/format';

	let { data } = $props();

	let open = $state(false);
	let editing = $state<Account | null>(null);
	let form = $state({ emailAddress: '', firstName: '', surname: '', password: '', admin: false, active: true });
	let errors = $state<Record<string, string>>({});
	let error = $state<string | null>(null);
	let saving = $state(false);

	let resetOpen = $state(false);
	let resetFor = $state<Account | null>(null);
	let resetPassword = $state('');
	let resetErrors = $state<Record<string, string>>({});

	const edit = (account: Account | null) => {
		editing = account;
		form = account
			? {
					emailAddress: account.emailAddress,
					firstName: account.firstName,
					surname: account.surname,
					password: '',
					admin: account.roles.includes('ROLE_ADMIN'),
					active: account.active
				}
			: { emailAddress: '', firstName: '', surname: '', password: '', admin: false, active: true };
		errors = {};
		error = null;
		open = true;
	};

	const save = async () => {
		saving = true;
		errors = {};
		error = null;
		const roles: Role[] = form.admin ? ['ROLE_USER', 'ROLE_ADMIN'] : ['ROLE_USER'];
		try {
			if (editing) {
				await api.put(`admin/accounts/${editing.id}`, {
					emailAddress: form.emailAddress,
					firstName: form.firstName,
					surname: form.surname,
					roles,
					active: form.active
				});
			} else {
				await api.post('admin/accounts', {
					emailAddress: form.emailAddress,
					firstName: form.firstName,
					surname: form.surname,
					password: form.password,
					roles
				});
			}
			open = false;
			toast.success('Benutzer gespeichert.');
			await invalidate('app:accounts');
		} catch (e) {
			if (e instanceof ApiError && (e.status === 422 || e.status === 409)) errors = e.fieldErrors;
			error = userMessage(e);
		} finally {
			saving = false;
		}
	};

	const doReset = async () => {
		if (!resetFor) return;
		saving = true;
		resetErrors = {};
		try {
			await api.put(`admin/accounts/${resetFor.id}/password`, { password: resetPassword });
			resetOpen = false;
			toast.success(`Passwort für ${resetFor.displayName} gesetzt. Alle Sitzungen wurden beendet.`);
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) resetErrors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			saving = false;
		}
	};
</script>

<svelte:head><title>Benutzer · Workers</title></svelte:head>

<PageHeader title="Benutzer" subtitle="Konten, Rollen und Zugänge verwalten.">
	{#snippet actions()}<Button onclick={() => edit(null)}><Plus class="size-4" />Neuer Benutzer</Button>{/snippet}
</PageHeader>

<div class="card overflow-x-auto">
	<table class="w-full text-sm">
		<thead class="bg-stone-50 text-left text-xs tracking-wide text-stone-500 uppercase">
			<tr
				><th class="px-4 py-2 font-medium">Name</th><th class="px-4 py-2 font-medium">E-Mail</th><th
					class="px-4 py-2 font-medium">Rolle</th
				><th class="px-4 py-2 font-medium">Letzte Anmeldung</th><th class="w-24"></th></tr
			>
		</thead>
		<tbody class="divide-y divide-stone-100">
			{#each data.accounts as account (account.id)}
				<tr class={account.active ? '' : 'text-stone-400'}>
					<td class="px-4 py-2.5 font-medium"
						>{account.displayName}{#if !account.active}
							<Badge tone="danger">deaktiviert</Badge>{/if}</td
					>
					<td class="px-4 py-2.5">{account.emailAddress}</td>
					<td class="px-4 py-2.5"
						>{#if account.roles.includes('ROLE_ADMIN')}<Badge tone="brand">Verwaltung</Badge>{:else}<Badge
								>Mitarbeiter</Badge
							>{/if}</td
					>
					<td class="px-4 py-2.5">{formatDateTime(account.lastLoginAt)}</td>
					<td class="px-2 text-right whitespace-nowrap">
						<button
							class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
							aria-label="{account.displayName} bearbeiten"
							onclick={() => edit(account)}><Pencil class="size-4" /></button
						>
						<button
							class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
							aria-label="Passwort für {account.displayName} setzen"
							onclick={() => {
								resetFor = account;
								resetPassword = '';
								resetErrors = {};
								resetOpen = true;
							}}><KeyRound class="size-4" /></button
						>
					</td>
				</tr>
			{/each}
		</tbody>
	</table>
</div>

<Dialog bind:open title={editing ? 'Benutzer bearbeiten' : 'Neuer Benutzer'}>
	<form
		id="account-form"
		class="flex flex-col gap-4"
		onsubmit={(e) => {
			e.preventDefault();
			save();
		}}
		novalidate
	>
		{#if error}<Alert tone="danger">{error}</Alert>{/if}
		<div class="grid gap-4 sm:grid-cols-2">
			<TextInput id="acc-first" label="Vorname" required bind:value={form.firstName} error={errors.firstName} />
			<TextInput id="acc-last" label="Nachname" required bind:value={form.surname} error={errors.surname} />
		</div>
		<TextInput
			id="acc-email"
			type="email"
			label="E-Mail-Adresse"
			required
			bind:value={form.emailAddress}
			error={errors.emailAddress}
			autocomplete="off"
		/>
		{#if !editing}
			<TextInput
				id="acc-password"
				type="password"
				label="Startpasswort"
				required
				bind:value={form.password}
				error={errors.password}
				hint="Mindestens 12 Zeichen. Bitte sicher übermitteln und Änderung empfehlen."
				autocomplete="new-password"
			/>
		{/if}
		<Checkbox
			id="acc-admin"
			label="Verwaltung (Administrator)"
			description="Stammdaten, Benutzer, alle Zeiterfassungen und Audit-Log."
			bind:checked={form.admin}
		/>
		{#if editing}<Checkbox
				id="acc-active"
				label="Konto aktiv"
				description="Deaktivierte Konten werden sofort abgemeldet."
				bind:checked={form.active}
			/>{/if}
		{#if errors.roles}<p class="text-sm text-red-600">{errors.roles}</p>{/if}
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button type="submit" form="account-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>

<Dialog bind:open={resetOpen} title="Passwort setzen" size="sm">
	<form
		id="reset-form"
		class="flex flex-col gap-4"
		onsubmit={(e) => {
			e.preventDefault();
			doReset();
		}}
		novalidate
	>
		<p class="text-sm text-stone-600">
			Neues Passwort für <strong>{resetFor?.displayName}</strong>. Alle bestehenden Sitzungen werden beendet.
		</p>
		<TextInput
			id="reset-password"
			type="password"
			label="Neues Passwort"
			required
			bind:value={resetPassword}
			error={resetErrors.password}
			autocomplete="new-password"
		/>
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (resetOpen = false)}>Abbrechen</Button>
		<Button type="submit" form="reset-form" loading={saving}>Passwort setzen</Button>
	{/snippet}
</Dialog>
