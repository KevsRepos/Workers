<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import ProductForm from "$lib/components/product/ProductForm.svelte";
import { fetchApi } from "$lib/fetchApi";
import type { Product } from "$lib/interfaces/Product.d.ts";
import type { Deposit } from "$lib/interfaces/Deposit.d.ts";

let { data }: { data: { product: Product; deposits: Deposit[] } } = $props();

let name = $state(data.product.name);
let salesPrice = $state(data.product.salesPrice ?? '');
let sellable = $state(data.product.sellable);
let rentable = $state(data.product.rentable);
let hasCrate = $state(data.product.quantityInCrate !== null);
let quantityInCrate = $state(data.product.quantityInCrate ?? 1);
let depositId = $state(data.product.deposit?.id ?? '');

let saving = $state(false);
let error = $state('');
let success = $state('');

async function handleSave() {
    saving = true;
    error = '';
    success = '';

    try {
        await fetchApi(`products/${data.product.id}`, 'PUT', {
            name,
            salesPrice: salesPrice !== '' ? Number(salesPrice) : null,
            sellable,
            rentable,
            depositId: depositId || null,
            quantityInCrate: hasCrate ? Number(quantityInCrate) : null
        });
        success = 'Gespeichert';
    } catch (e: any) {
        error = e.message || 'Fehler beim Speichern';
    } finally {
        saving = false;
    }
}
</script>

<PageHeadline>Artikel bearbeiten</PageHeadline>

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


