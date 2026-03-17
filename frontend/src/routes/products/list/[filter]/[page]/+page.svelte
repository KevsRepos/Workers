<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import Pagination from "$lib/components/Pagination.svelte";
import { CirclePlus, Pen } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";
import { centToEuro } from "$lib/functions/helpers";
import { goto } from "$app/navigation";

let { data } = $props();

const filterOptions = [
    { value: 'all', label: 'Alle' },
    { value: 'with-deposit', label: 'Mit Pfand' },
    { value: 'without-deposit', label: 'Ohne Pfand' },
    { value: 'sellable', label: 'Verkäuflich' },
    { value: 'rentable', label: 'Leihartikel' },
];

let filter = $state(data.filter);

function onFilterChange() {
    goto(`/products/list/${filter}/1`);
}
</script>

<Navigation class="mb-3 no-scrollbar">
    <Navigation.Menu class="overflow-x-auto">
        <Navigation.TriggerAnchor href="/products/create">
            <CirclePlus />
            <Navigation.TriggerText>Anlegen</Navigation.TriggerText>
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor class="border">
            <select bind:value={filter} onchange={onFilterChange}>
                {#each filterOptions as opt}
                    <option value={opt.value}>{opt.label}</option>
                {/each}
            </select>
        </Navigation.TriggerAnchor>
    </Navigation.Menu>
</Navigation>

<PageHeadline>Artikel</PageHeadline>

<main class="p-4">
    <div class="pb-2 font-bold">{data.products.total} Artikel gesamt</div>

    <div class="flex flex-col gap-2">
        {#each data.products.data as product}
            <div class=" bg-surface-100-900 flex w-full rounded">
                <a class="flex items-center py-2 px-6" href="/products/{product.id}">
                    <Pen />
                </a>
                <div class="flex flex-col py-2">
                    <div class="font-bold">{product.name}</div>
                    <div>
                        {#if product.sellable}
                            Verkäuflich
                        {:else if product.rentable}
                            Leihartikel
                        {/if}
                    </div>
                    <div>
                        {#if product.quantityInCrate}
                            {product.quantityInCrate} Stk. in Kiste
                        {:else}
                            Einzelartikel
                        {/if}
                    </div>
                    <div>
                        {#if product.deposit}
                            {#if product.deposit.crateAmount}
                                {centToEuro(product.deposit.singleAmount)}€ * {centToEuro(product.deposit.crateAmount)}€ Pfand
                            {:else}
                                {centToEuro(product.deposit.singleAmount)}€
                            {/if}
                        {:else}
                            Kein Pfand
                        {/if}
                    </div>
                </div>
            </div>
        {/each}
    </div>

    <div class="mt-4 flex justify-center">
        <Pagination data={data.products} href={`/products/list/${filter}`} />
    </div>
</main>