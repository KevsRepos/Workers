<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import SearchSelectionBox from "../SearchSelectionBox.svelte";

let { selectedProducts = $bindable(), deliveryNoteForm } = $props();

let inputElement: HTMLInputElement | null = $state(null);

let productInput = $state('');

let quantityInput: HTMLInputElement | null = $state(null);

let quantity = $state();

let searchTimeout: ReturnType<typeof setTimeout>;

let searchItems = $state([]);

let currentProduct: { id: string; name: string } | null = $state(null);

const searchProduct = async (event: Event) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(async () => {
        const query = (event.target as HTMLInputElement).value;

        if (query.length < 2) {
            searchItems = [];
            return;
        }
        
        const json = await fetchApi(`products/search?productName=${encodeURIComponent(query)}`, 'GET');
        
        searchItems = json;
    }, 300);
}

const selectProduct = (product: { id: string; name: string }) => {
    deliveryNoteForm.addProduct(product.id, 1, product.name);
    currentProduct = product;
    quantityInput?.focus();

    productInput = product.name;

    searchItems = [];
}

const setQuantity = (event: KeyboardEvent) => {
    if(event.key !== 'Enter') return;

    deliveryNoteForm.updateQuantity(currentProduct?.id, quantity);

    productInput = '';
    
    inputElement?.focus();

    quantity = '';
}
</script>

<SearchSelectionBox bind:inputElement={inputElement} bind:input={productInput} bind:items={searchItems} searchItem={searchProduct} onSelect={selectProduct} label="Artikel" placeholder="Krombacher, Coca Cola...">
    {#snippet content(item, onSelect)}
        <button onclick={() => onSelect(item)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{item.name}</button>
    {/snippet}
</SearchSelectionBox>

<label class="label mt-2">
    <span class="label-text">Menge</span>
    <input type="number" class="input bg-surface-50-950" bind:this={quantityInput} bind:value={quantity} onkeydown={setQuantity} />
</label>