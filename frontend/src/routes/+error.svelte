<script lang="ts">
	import { page } from '$app/state';
	import Button from '$lib/components/ui/Button.svelte';

	const titles: Record<number, string> = {
		403: 'Keine Berechtigung',
		404: 'Nicht gefunden',
		500: 'Etwas ist schiefgelaufen',
		503: 'Server nicht erreichbar'
	};
</script>

<svelte:head><title>{titles[page.status] ?? 'Fehler'} · Workers</title></svelte:head>

<div class="mx-auto flex max-w-md flex-col items-center gap-3 py-20 text-center">
	<p class="text-5xl font-semibold text-brand-700">{page.status}</p>
	<h1 class="text-xl">{titles[page.status] ?? 'Fehler'}</h1>
	<p class="text-stone-600">{page.error?.message}</p>
	{#if page.error?.requestId && page.status >= 500}
		<p class="text-xs text-stone-500">Referenz für den Support: <code>{page.error.requestId}</code></p>
	{/if}
	<div class="mt-4 flex gap-2">
		<Button variant="secondary" onclick={() => history.back()}>Zurück</Button>
		<Button href="/">Zur Übersicht</Button>
	</div>
</div>
