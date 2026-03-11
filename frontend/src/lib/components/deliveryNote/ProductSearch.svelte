<script lang="ts">
import { fetchApi } from '$lib/fetchApi';
import { Combobox, Portal, type ComboboxRootProps, useListCollection } from '@skeletonlabs/skeleton-svelte';

let { selectedProducts = $bindable(), deliveryNoteForm, jump } = $props();

let products = $state([]);

let currentProduct: { id: number; name: string; quantity: number } | null = $state(null);

let quantityInput = $state<HTMLInputElement>();
let comboboxWrapper = $state<HTMLDivElement>();

let searchTimeout: ReturnType<typeof setTimeout>;

const collection = $derived(useListCollection({ 
    items: products,
    itemToString: (item) => item.name,
    itemToValue: (item) => item
}));

const searchProduct: ComboboxRootProps['onInputValueChange'] = async (event) => {
    const query = event.inputValue;
    
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

const selectProduct: ComboboxRootProps['onSelect'] = (event) => {
    currentProduct = event.itemValue;

    deliveryNoteForm.addProduct(event.itemValue.id, 1, event.itemValue.name);

    quantityInput?.focus();
}

const enterQuantity = () => {
    const product = selectedProducts.find(p => p.productId === currentProduct.id);
    if (product) {
        product.quantity = parseInt(quantityInput.value) || 1;
    }

    jump();

    if (quantityInput) {
        quantityInput.value = '';
    }
}
</script>

<div bind:this={comboboxWrapper}>
<Combobox ids={{input: 'product-search-input'}} {collection} onSelect={selectProduct} onInputValueChange={searchProduct} placeholder="Artikel" multiple>
    <Combobox.Label>Artikel</Combobox.Label>
    <Combobox.Control>
        <Combobox.Input />
    </Combobox.Control>
    <Portal>
        <Combobox.Positioner>
            {#if products.length}
                <Combobox.Content>
                    {#each products as item}
                        <Combobox.Item item={item}>
                            <Combobox.ItemText>{item.name}</Combobox.ItemText>
                            <Combobox.ItemIndicator />
                        </Combobox.Item>
                    {/each}
                </Combobox.Content>
            {/if}
        </Combobox.Positioner>
    </Portal>
</Combobox>
</div>

<label class="label">
    <span class="label-text">Menge</span>
    <input onfocusout={enterQuantity} onkeydown={(e) => { if (e.key === 'Enter') { enterQuantity(); }}} class="input" type="number" min="1" bind:this={quantityInput} />
</label>