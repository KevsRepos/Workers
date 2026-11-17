<script lang="ts">
	import { enhance } from '$app/forms';
	import { api } from '$lib/api/client';
	import { userMessage } from '$lib/api/errors';
	import type { ReminderSettings } from '$lib/api/types';
	import { translate } from '$lib/api/errors';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Checkbox from '$lib/components/ui/Checkbox.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import { toast } from '$lib/stores/toast.svelte';
	import { WEEKDAYS_SHORT } from '$lib/utils/format';

	let { data, form } = $props();

	// svelte-ignore state_referenced_locally
	let reminder = $state<ReminderSettings>({ ...data.reminder });
	let savingReminder = $state(false);
	let changingPassword = $state(false);

	const passwordErrors = $derived((form?.passwordErrors ?? {}) as Record<string, string>);

	const toggleDay = (day: number) => {
		reminder.weekdays = reminder.weekdays.includes(day)
			? reminder.weekdays.filter((d) => d !== day)
			: [...reminder.weekdays, day].sort();
	};

	const saveReminder = async () => {
		savingReminder = true;
		try {
			reminder = await api.put<ReminderSettings>('time-tracking/reminder', reminder);
			toast.success('Erinnerung gespeichert.');
		} catch (e) {
			toast.error(userMessage(e));
		} finally {
			savingReminder = false;
		}
	};
</script>

<svelte:head><title>Einstellungen · Workers</title></svelte:head>

<PageHeader title="Einstellungen" subtitle="{data.user?.name} · {data.user?.email}" />

<div class="grid max-w-4xl gap-6 lg:grid-cols-2">
	<section class="card p-4 sm:p-6" aria-labelledby="password-title">
		<h2 id="password-title" class="mb-1 text-base">Passwort ändern</h2>
		<p class="mb-4 text-sm text-stone-500">
			Mindestens 12 Zeichen. Eine Passphrase aus mehreren Wörtern ist sicher und gut zu merken. Alle anderen Sitzungen
			werden abgemeldet.
		</p>
		{#if form?.passwordChanged}<div class="mb-4"><Alert tone="success">Passwort geändert.</Alert></div>{/if}
		{#if form?.passwordError}<div class="mb-4"><Alert tone="danger">{form.passwordError}</Alert></div>{/if}
		<form
			method="POST"
			action="?/password"
			class="flex flex-col gap-4"
			use:enhance={() => {
				changingPassword = true;
				return async ({ update }) => {
					await update();
					changingPassword = false;
				};
			}}
		>
			<TextInput
				id="currentPassword"
				type="password"
				label="Aktuelles Passwort"
				autocomplete="current-password"
				required
				error={passwordErrors.currentPassword ? translate(passwordErrors.currentPassword) : null}
			/>
			<TextInput
				id="newPassword"
				type="password"
				label="Neues Passwort"
				autocomplete="new-password"
				required
				minlength={12}
				error={passwordErrors.newPassword ? translate(passwordErrors.newPassword) : null}
			/>
			<TextInput
				id="confirmNewPassword"
				type="password"
				label="Neues Passwort wiederholen"
				autocomplete="new-password"
				required
				error={passwordErrors.confirmNewPassword ? translate(passwordErrors.confirmNewPassword) : null}
			/>
			<Button type="submit" loading={changingPassword} class="self-start">Passwort ändern</Button>
		</form>
	</section>

	<section class="card p-4 sm:p-6" aria-labelledby="reminder-title">
		<h2 id="reminder-title" class="mb-1 text-base">Erinnerung Zeiterfassung</h2>
		<p class="mb-4 text-sm text-stone-500">Per E-Mail erinnern, wenn für den Tag noch keine Zeit erfasst wurde.</p>
		<form
			class="flex flex-col gap-4"
			onsubmit={(e) => {
				e.preventDefault();
				saveReminder();
			}}
		>
			<Checkbox id="reminder-enabled" label="Erinnerung aktivieren" bind:checked={reminder.enabled} />
			<fieldset disabled={!reminder.enabled} class="flex flex-col gap-4 disabled:opacity-50">
				<TextInput id="reminder-time" type="time" label="Uhrzeit" bind:value={reminder.time} class="max-w-40" />
				<div>
					<p class="mb-2 text-sm font-medium text-stone-700">Wochentage</p>
					<div class="flex flex-wrap gap-2">
						{#each WEEKDAYS_SHORT as label, index (label)}
							<button
								type="button"
								aria-pressed={reminder.weekdays.includes(index + 1)}
								onclick={() => toggleDay(index + 1)}
								class="size-10 rounded-full text-sm font-medium ring-1 ring-inset {reminder.weekdays.includes(index + 1)
									? 'bg-brand-700 text-white ring-brand-700'
									: 'bg-white text-stone-700 ring-stone-300'}">{label}</button
							>
						{/each}
					</div>
				</div>
			</fieldset>
			<Button type="submit" loading={savingReminder} class="self-start">Speichern</Button>
		</form>
	</section>
</div>
