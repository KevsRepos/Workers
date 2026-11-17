<script lang="ts">
	import { page } from '$app/state';
	import { ChevronRight, Plus } from '@lucide/svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import SearchInput from '$lib/components/ui/SearchInput.svelte';
	import { crateDepositCents } from '$lib/domain/deliveryNote';
	import { formatCents } from '$lib/utils/format';
	import { highlightVolumePart } from '$lib/utils/volumeHighlighter';

	let { data } = $props();

	const filters = [
		{ value: 'all', label: 'Alle' },
		{ value: 'sellable', label: 'Verkauf' },
		{ value: 'rentable', label: 'Verleih' },
		{ value: 'with-deposit', label: 'Mit Pfand' },
		{ value: 'without-deposit', label: 'Ohne Pfand' },
		{ value: 'archived', label: 'Archiviert' }
	];

	const filterHref = (value: string) => {
		const url = new URL(page.url);
		url.searchParams.set('filter', value);
		url.searchParams.delete('page');
		return url.pathname + url.search;
	};
</script>

<svelte:head><title>Artikel · Workers</title></svelte:head>

<PageHeader title="Artikel">
	{#snippet actions()}
		{#if data.user?.isAdmin}<Button href="/products/new"><Plus class="size-4" />Neuer Artikel</Button>{/if}
	{/snippet}
</PageHeader>

<div class="mb-4 flex flex-col gap-3">
	<SearchInput placeholder="Artikel suchen …" />
	<div class="flex flex-wrap gap-2">
		{#each filters as f (f.value)}
			<a
				href={filterHref(f.value)}
				class="rounded-full px-3 py-1 text-sm font-medium ring-1 ring-inset {data.filter === f.value
					? 'bg-brand-700 text-white ring-brand-700'
					: 'bg-white text-stone-700 ring-stone-300 hover:bg-stone-50'}">{f.label}</a
			>
		{/each}
	</div>
</div>

<div class="card divide-y divide-stone-100 overflow-hidden">
	{#each data.products.items as product (product.id)}
		<a href="/products/{product.id}" class="group flex items-center gap-4 px-4 py-3 hover:bg-stone-50">
			<div class="min-w-0 flex-1">
				<p class="truncate font-medium">
					{#each highlightVolumePart(product.name) as part, i (i)}<span class={part.className}>{part.text}</span>{/each}
				</p>
				<div class="mt-1 flex flex-wrap gap-1.5">
					{#if product.sellable}<Badge tone="brand">Verkauf</Badge>{/if}
					{#if product.rentable}<Badge tone="info">Verleih</Badge>{/if}
					{#if product.deposit}<Badge
							>Pfand {formatCents(crateDepositCents(product.deposit, product.quantityInCrate))}</Badge
						>{/if}
					{#if product.archived}<Badge tone="warning">Archiviert</Badge>{/if}
				</div>
			</div>
			<span class="text-sm font-medium text-stone-700">{formatCents(product.salesPrice)}</span>
			<ChevronRight class="size-5 text-stone-300 group-hover:text-stone-500" aria-hidden="true" />
		</a>
	{:else}
		<EmptyState title="Keine Artikel gefunden" />
	{/each}
</div>
<Pagination page={data.products.page} pages={data.products.pages} total={data.products.total} />
