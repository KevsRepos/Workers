<script lang="ts">
	import { enhance } from '$app/forms';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';

	let { data, form } = $props();
	let submitting = $state(false);
</script>

<svelte:head><title>Anmelden · Workers</title></svelte:head>

<div class="flex min-h-dvh items-center justify-center bg-brand-950 px-4 py-12">
	<div class="w-full max-w-sm">
		<p class="mb-8 text-center font-brand text-5xl text-white">Workers</p>
		<div class="card p-6 sm:p-8">
			<h1 class="mb-1 text-xl">Anmelden</h1>
			<p class="mb-6 text-sm text-stone-500">Mit deinem Firmenkonto anmelden.</p>

			{#if data.expired && !form?.error}
				<div class="mb-4"><Alert tone="info">Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.</Alert></div>
			{/if}
			{#if form?.error}
				<div class="mb-4"><Alert tone="danger">{form.error}</Alert></div>
			{/if}

			<form
				method="POST"
				class="flex flex-col gap-4"
				use:enhance={() => {
					submitting = true;
					return async ({ update }) => {
						await update({ reset: false });
						submitting = false;
					};
				}}
			>
				<input type="hidden" name="next" value={data.next} />
				<TextInput
					id="email"
					label="E-Mail-Adresse"
					type="email"
					autocomplete="username"
					required
					value={form?.email ?? ''}
				/>
				<TextInput id="password" label="Passwort" type="password" autocomplete="current-password" required />
				<Button type="submit" size="lg" loading={submitting} class="mt-2 w-full">Anmelden</Button>
			</form>
		</div>
		<p class="mt-6 text-center text-xs text-brand-200">Passwort vergessen? Bitte wende dich an die Verwaltung.</p>
	</div>
</div>
