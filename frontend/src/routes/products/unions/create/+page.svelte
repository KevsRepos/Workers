<script lang="ts">
import { goto } from "$app/navigation";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import ProductUnionForm from "$lib/components/productUnion/ProductUnionForm.svelte";
import { fetchApi } from "$lib/fetchApi";

let name = $state('');
let selectedProducts = $state<{ id: string; name: string }[]>([]);
let saving = $state(false);
let error = $state('');

async function handleSave() {
    saving = true;
    error = '';

    try {
        const result = await fetchApi('product-unions', 'POST', {
            name,
            productIds: selectedProducts.map(p => p.id)
        });

        goto(`/products/unions/${result.data.id}`);
    } catch (e: any) {
        error = e.message || 'Fehler beim Erstellen';
    } finally {
        saving = false;
    }
}
</script>

<PageHeadline>Gebinde erstellen</PageHeadline>

<ProductUnionForm
    bind:name
    bind:selectedProducts
    onSave={handleSave}
    {saving}
    {error}
/>
