<script lang="ts">
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import ProductUnionForm from "$lib/modules/product-union/components/ProductUnionForm.svelte";
import { updateProductUnion } from "$lib/modules/product-union/api";

let { data } = $props();

let name = $state(data.productUnion.name);
let selectedProducts = $state<{ id: string; name: string }[]>(
    data.productUnion.products.map((p: any) => ({ id: p.product.id, name: p.product.name }))
);

let saving = $state(false);
let error = $state('');
let success = $state('');

async function handleSave() {
    saving = true;
    error = '';
    success = '';

    try {
        await updateProductUnion(data.productUnion.id, {
            name,
            productIds: selectedProducts.map(p => p.id)
        });
        success = 'Gespeichert';
    } catch (e: any) {
        error = e.message || 'Fehler beim Speichern';
    } finally {
        saving = false;
    }
}
</script>

<PageHeadline>Einheit bearbeiten</PageHeadline>

<ProductUnionForm
    bind:name
    bind:selectedProducts
    onSave={handleSave}
    {saving}
    {error}
    {success}
/>
