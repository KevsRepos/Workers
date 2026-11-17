<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/state';
	import { Plus } from '@lucide/svelte';
	import DeliveryNoteCard from '$lib/components/DeliveryNoteCard.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import SearchInput from '$lib/components/ui/SearchInput.svelte';
	import { STATUS_OPTIONS } from '$lib/domain/deliveryNote';

	let { data } = $props();

	const tabs = $derived([
		...STATUS_OPTIONS.map((s) => ({ value: String(s.value), label: s.label, count: data.counts[s.value] ?? 0 })),
		{ value: 'all', label: 'Alle', count: null }
	]);

	const tabHref = (value: string) => {
		const url = new URL(page.url);
		url.searchParams.set('status', value);
		url.searchParams.delete('page');
		return url.pathname + url.search;
	};

	const setDate = (key: 'from' | 'to', value: string) => {
		const url = new URL(page.url);
		if (value) url.searchParams.set(key, value);
		else url.searchParams.delete(key);
		url.searchParams.delete('page');
		goto(url.pathname + url.search, { keepFocus: true, noScroll: true });
	};
</script>

<svelte:head><title>Lieferscheine · Workers</title></svelte:head>

<PageHeader title="Lieferscheine">
	{#snippet actions()}
		<Button href="/delivery-notes/new"><Plus class="size-4" />Neuer Lieferschein</Button>
	{/snippet}
</PageHeader>

<nav class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Status">
	<ul class="flex gap-1 border-b border-stone-200">
		{#each tabs as tab (tab.value)}
			<li>
				<a
					href={tabHref(tab.value)}
					class="flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium whitespace-nowrap {data.status ===
					tab.value
						? 'border-brand-700 text-brand-800'
						: 'border-transparent text-stone-500 hover:text-stone-800'}"
					aria-current={data.status === tab.value ? 'page' : undefined}
				>
					{tab.label}
					{#if tab.count !== null}<span class="rounded-full bg-stone-100 px-1.5 text-xs text-stone-600"
							>{tab.count}</span
						>{/if}
				</a>
			</li>
		{/each}
	</ul>
</nav>

<div class="mb-4 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
	<SearchInput placeholder="Kunde, Nummer, Hinweis, Zuständig …" />
	<label class="flex items-center gap-2 text-sm text-stone-600"
		>von <input
			type="date"
			class="field-input"
			value={data.from}
			onchange={(e) => setDate('from', e.currentTarget.value)}
		/></label
	>
	<label class="flex items-center gap-2 text-sm text-stone-600"
		>bis <input
			type="date"
			class="field-input"
			value={data.to}
			onchange={(e) => setDate('to', e.currentTarget.value)}
		/></label
	>
</div>

<div class="card divide-y divide-stone-100 overflow-hidden">
	{#each data.notes.items as note (note.id)}
		<DeliveryNoteCard {note} />
	{:else}
		<EmptyState
			title="Keine Lieferscheine gefunden"
			description="Filter anpassen oder einen neuen Lieferschein anlegen."
		>
			{#snippet action()}<Button href="/delivery-notes/new" variant="secondary"
					><Plus class="size-4" />Neuer Lieferschein</Button
				>{/snippet}
		</EmptyState>
	{/each}
</div>
<Pagination page={data.notes.page} pages={data.notes.pages} total={data.notes.total} />
