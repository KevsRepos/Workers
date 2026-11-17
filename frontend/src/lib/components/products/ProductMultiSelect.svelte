<script lang="ts">
	import { X } from '@lucide/svelte';
	import { api, query } from '$lib/api/client';
	import type { Page, Product } from '$lib/api/types';
	import Combobox from '$lib/components/ui/Combobox.svelte';

	let {
		selected = $bindable(),
		error = null,
		id = 'product-multi'
	}: { selected: Array<Pick<Product, 'id' | 'name'>>; error?: string | null; id?: string } = $props();

	const search = async (q: string) =>
		(await api.get<Page<Product>>(`products${query({ q, limit: 12 })}`)).items.filter(
			(p) => !selected.some((s) => s.id === p.id)
		);
</script>

<div class="flex flex-col gap-3">
	<Combobox
		{id}
		label="Artikel hinzufügen"
		placeholder="Artikel suchen …"
		{search}
		itemKey={(p) => p.id}
		onselect={(p) => selected.push({ id: p.id, name: p.name })}
		{error}
	>
		{#snippet item(product)}{product.name}{/snippet}
	</Combobox>
	{#if selected.length > 0}
		<ul class="flex flex-wrap gap-2">
			{#each selected as product, index (product.id)}
				<li
					class="flex items-center gap-1 rounded-full bg-brand-50 py-1 pr-1 pl-3 text-sm text-brand-900 ring-1 ring-brand-200"
				>
					{product.name}
					<button
						type="button"
						class="rounded-full p-0.5 hover:bg-brand-100"
						aria-label="{product.name} entfernen"
						onclick={() => selected.splice(index, 1)}><X class="size-3.5" /></button
					>
				</li>
			{/each}
		</ul>
	{:else}
		<p class="text-sm text-stone-500">Noch keine Artikel ausgewählt.</p>
	{/if}
</div>
