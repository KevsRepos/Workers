<script lang="ts">
import { fetchApi } from '$lib/fetchApi';

let { selectedProducts = $bindable(), deliveryNoteForm } = $props();

let products: { id: number; name: string; quantity: number }[] = $state([]);

let currentProduct: { id: number; name: string; quantity: number } | null = $state(null);

let quantityInput = $state<HTMLInputElement>();

let productSearchInput: HTMLInputElement;

let searchTimeout: ReturnType<typeof setTimeout>;

const searchProduct = async (event: Event & { currentTarget: EventTarget & HTMLInputElement }) => {
    const query = event.currentTarget.value;
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            products = [];
            return;
        }
        
        const json = await fetchApi(`products/search?productName=${encodeURIComponent(query)}`, 'GET');
        
        products = json;
    }, 300);
}

const selectProduct = (product: { id: number; name: string; quantity: number }) => {
    currentProduct = product;
    deliveryNoteForm.addProduct(product.id, 1, product.name);
    quantityInput?.focus();

    productSearchInput.value = product.name;

    products = [];
}

const enterQuantity = () => {
    if (currentProduct === null) return;

    const currentProductId = currentProduct.id;
    const product = selectedProducts.find((p: { productId: number; quantity: number }) => p.productId === currentProductId);

    if (product && quantityInput) {
        product.quantity = parseInt(quantityInput.value) || 1;
    }

    productSearchInput.value = '';
    productSearchInput.focus();
    currentProduct = null;

    if (quantityInput) {
        quantityInput.value = '';
    }
}
</script>

<div class="relative">
    <label class="label">
        <span class="label-text">Artikel</span>
        <input class="input bg-surface-50-950" type="text" oninput={searchProduct} bind:this={productSearchInput} />
    </label>

    {#if products.length}
        <div class="absolute flex flex-col bg-surface-50-950 border w-full max-h-[50vh] overflow-y-auto border-gray-300 rounded-md shadow-lg z-50">
            {#each products as product}
                <button onclick={() => selectProduct(product)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{product.name}</button>
            {/each}
        </div>
    {/if}

    <label class="label">
        <span class="label-text">Menge</span>
        <input onfocusout={enterQuantity} onkeydown={(e) => { if (e.key === 'Enter') { enterQuantity(); }}} class="input bg-surface-50-950" type="number" min="1" bind:this={quantityInput} />
    </label>
</div>