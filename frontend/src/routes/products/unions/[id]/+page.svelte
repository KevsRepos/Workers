<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import ProductUnionForm from "$lib/components/productUnion/ProductUnionForm.svelte";
import { fetchApi } from "$lib/fetchApi";

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
        await fetchApi(`product-unions/${data.productUnion.id}`, 'PUT', {
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
