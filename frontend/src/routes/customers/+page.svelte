<script lang="ts">
	import { Building2, ChevronRight, Plus, UserRound } from '@lucide/svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import SearchInput from '$lib/components/ui/SearchInput.svelte';

	let { data } = $props();
</script>

<svelte:head><title>Kunden · Workers</title></svelte:head>

<PageHeader title="Kunden">
	{#snippet actions()}<Button href="/customers/new"><Plus class="size-4" />Neuer Kunde</Button>{/snippet}
</PageHeader>

<div class="mb-4"><SearchInput placeholder="Name, Firma, E-Mail oder Telefon" /></div>

<div class="card divide-y divide-stone-100 overflow-hidden">
	{#each data.customers.items as customer (customer.id)}
		<a href="/customers/{customer.id}" class="group flex items-center gap-4 px-4 py-3 hover:bg-stone-50">
			<div class="rounded-full bg-stone-100 p-2 text-stone-500" aria-hidden="true">
				{#if customer.company}<Building2 class="size-4" />{:else}<UserRound class="size-4" />{/if}
			</div>
			<div class="min-w-0 flex-1">
				<p class="truncate font-medium">{customer.displayName}</p>
				<p class="truncate text-sm text-stone-500">
					{[customer.defaultAddress, customer.phone, customer.email].filter(Boolean).join(' · ') || '–'}
				</p>
			</div>
			<ChevronRight class="size-5 text-stone-300 group-hover:text-stone-500" aria-hidden="true" />
		</a>
	{:else}
		<EmptyState title="Keine Kunden gefunden" description="Suchbegriff anpassen oder neuen Kunden anlegen." />
	{/each}
</div>
<Pagination page={data.customers.page} pages={data.customers.pages} total={data.customers.total} />
