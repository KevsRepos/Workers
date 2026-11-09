<script lang="ts">
import { goto } from "$app/navigation";
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import ProductForm from "$lib/modules/product/components/ProductForm.svelte";
import { createProduct } from "$lib/modules/product/api";
import type { Deposit } from "$lib/modules/product/model/Deposit.d.ts";

let { data }: { data: { deposits: Deposit[] } } = $props();

let name = $state('');
let salesPrice: number | string = $state('');
let sellable = $state(true);
let rentable = $state(false);
let hasCrate = $state(false);
let quantityInCrate = $state(1);
let depositId = $state('');

let saving = $state(false);
let error = $state('');
let success = $state('');

async function handleSave() {
    saving = true;
    error = '';
    success = '';

    try {
        await createProduct({
            name,
            salesPrice: salesPrice !== '' ? Number(salesPrice) : null,
            sellable,
            rentable,
            depositId: depositId || null,
            quantityInCrate: hasCrate ? Number(quantityInCrate) : null
        });
        goto('/products/list/all/1');
    } catch (e: any) {
        error = e.message || 'Fehler beim Erstellen';
    } finally {
        saving = false;
    }
}
</script>

<PageHeadline>Artikel erstellen</PageHeadline>

<ProductForm
    bind:name
    bind:salesPrice
    bind:sellable
    bind:rentable
    bind:hasCrate
    bind:quantityInCrate
    bind:depositId
    deposits={data.deposits}
    onSave={handleSave}
    {saving}
    {error}
    {success}
/>
