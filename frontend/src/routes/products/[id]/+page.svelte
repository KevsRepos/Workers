<script lang="ts">
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import ProductForm from "$lib/modules/product/components/ProductForm.svelte";
import { updateProduct } from "$lib/modules/product/api";
import type { Product } from "$lib/modules/product/model/Product.d.ts";
import type { Deposit } from "$lib/modules/product/model/Deposit.d.ts";

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
        await updateProduct(data.product.id, {
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


