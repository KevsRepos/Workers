<script lang="ts">
import { browser } from "$app/env";
import { addProductToDeliveryNote, updateDeliveryNoteProductQuantity } from "$lib/deliveryNote/actions";
import { fetchApi } from "$lib/fetchApi";
import { volumeHighlighter } from "$lib/volumeHighlighter";
import { CircleX } from "@lucide/svelte";
import Checkbox from "../elements/Checkbox.svelte";
import SearchSelectionBox from "../SearchSelectionBox.svelte";

let { selectedProducts = $bindable(), deliveryNoteForm, removedProductIds = [] } = $props();

const removeProduct = (index: number) => {
    const removed = deliveryNoteForm.products.splice(index, 1);

    if (removed[0].id) {
        removedProductIds.push(removed[0].id);
    }
}

const getQuantityInputStorage = (): boolean => {
    if(!browser) return false;

    console.log(window.localStorage.getItem('deliveryNoteHideQuantityInput') === '1');

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
    addProductToDeliveryNote(deliveryNoteForm, product.id, null, product.name);
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
    if (!currentProduct?.id || typeof quantity !== 'number') {
        return;
    }

    updateDeliveryNoteProductQuantity(deliveryNoteForm, currentProduct.id, quantity);

    productInput = '';
    
    inputElement?.focus();

    quantity = '';
}
</script>
<Checkbox class="mb-4 flex-row" label="Mengen Eingabe verbergen" bind:checked={
    () => hideQuantityInput,
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

<div class="flex flex-col gap-2 mt-2 divider-y overflow-y-auto">
    {#each deliveryNoteForm.products as product, index}
        <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
            <div class="flex gap-2 items-center">
                <button onclick={() => removeProduct(index)} type="button">
                    <CircleX />
                </button>
                <div>{product.name}</div>
            </div>

            <input placeholder="Menge" class="input bg-surface-300-700 w-32" type="number" min="1" bind:value={deliveryNoteForm.products[index].quantity} />
        </div>
    {/each}
</div>