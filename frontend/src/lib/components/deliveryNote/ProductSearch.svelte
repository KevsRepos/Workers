<script lang="ts">
import { browser } from "$app/env";
import { fetchApi } from "$lib/fetchApi";
import { volumeHighlighter } from "$lib/volumeHighlighter";
import Checkbox from "../elements/Checkbox.svelte";
import SearchSelectionBox from "../SearchSelectionBox.svelte";

let { selectedProducts = $bindable(), deliveryNoteForm } = $props();

const getQuantityInputStorage = (): boolean => {
    if(!browser) return false;

    return window.localStorage.getItem('deliveryNoteHideQuantityInput') === '1' ? true : false;
}
const setQuantityInputStorage = (v: boolean) => {
    if(!browser) return;

    window.localStorage.setItem('deliveryNoteHideQuantityInput', v ? '1' : '0');
    hideQuantityInput = v;
}

let hideQuantityInput = $state(getQuantityInputStorage());

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
    deliveryNoteForm.addProduct(product.id, null, product.name);
    currentProduct = product;
    quantityInput?.focus();

    if(hideQuantityInput) {
        productInput = '';
        inputElement?.focus();
    } else {
        productInput = product.name;
    }

    searchItems = [];
}

const setQuantity = () => {
    deliveryNoteForm.updateQuantity(currentProduct?.id, quantity);

    productInput = '';
    
    inputElement?.focus();

    quantity = '';
}
</script>
<Checkbox class="mb-4 flex-row-reverse" label="Mengen Eingabe verbergen" bind:checked={
    () => getQuantityInputStorage(),
    (v: boolean) => setQuantityInputStorage(v)
}/>

<SearchSelectionBox bind:inputElement={inputElement} bind:input={productInput} bind:items={searchItems} searchItem={searchProduct} onSelect={selectProduct} label="Artikel" placeholder="Krombacher, Coca Cola...">
    {#snippet content(item, onSelect)}
        <button onclick={() => onSelect(item)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{@html volumeHighlighter(item.name)}</button>
    {/snippet}
</SearchSelectionBox>

{#if !hideQuantityInput}
    <label class="label mt-2">
        <span class="label-text">Menge</span>
        <input type="number" class="input bg-surface-50-950" bind:this={quantityInput} bind:value={quantity} onfocusout={setQuantity} onkeydown={(e) => e.key === 'Enter' && setQuantity()} />
    </label>
{/if}