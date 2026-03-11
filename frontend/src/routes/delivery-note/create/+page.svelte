<script lang="ts">
import { Combobox, Portal, useListCollection, type ComboboxRootProps } from "@skeletonlabs/skeleton-svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { deliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";
import ProductSearch from "./ProductSearch.svelte";

let searchTimeout: ReturnType<typeof setTimeout>;
let customers: Array<any> = $state([]);

let scrollAnchor1: HTMLSpanElement = $state();
let scrollAnchor2: HTMLSpanElement = $state();
let scrollAnchor3: HTMLSpanElement = $state();
let scrollAnchor4: HTMLSpanElement = $state();

let deliveryDatePicker: HTMLInputElement = $state();

let productSearchFocus = $state(false);

const collection = $derived(useListCollection({ 
    items: customers,
    itemToString: (item) => item.firstName + ' ' + item.surname,
    itemToValue: (item) => item
}));

const searchCustomer = async (event: Event) => {
    const query = (event.target as HTMLInputElement).value;
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            customers = [];
            return;
        }
        
        const json = await fetchApi(`customers/search?customerName=${encodeURIComponent(query)}`, 'GET');
        
        customers = json;
    }, 300);
}

const selectCustomer: ComboboxRootProps['onSelect'] = (event) => {
    deliveryNoteForm.setCustomer(event.value);

    scrollAnchor2.scrollIntoView({ behavior: 'smooth' });
}

const deliverySelected = () => {
    scrollAnchor3.scrollIntoView({ behavior: 'smooth' });

    deliveryDatePicker.focus();
    deliveryDatePicker.showPicker();
}

const dateSelected = () => {
    scrollAnchor4.scrollIntoView({ behavior: 'smooth' });

    productSearchFocus = true;
}
</script>

<main class="px-4" style="min-height: 4000px">
    <h1 class="mt-4 mb-2 text-center font-bold">Lieferschein erstellen</h1>

    <form method="POST" class="flex flex-col gap-2">

        <span bind:this={scrollAnchor1}></span>
        <div class="pt-9 pb-18 border-b border-surface-200-800">
            <Combobox collection={collection} onSelect={selectCustomer} placeholder="Kunde auswählen" class="w-full">
                <Combobox.Label>Kunde</Combobox.Label>
                <Combobox.Control>
                    <Combobox.Input oninput={searchCustomer} />
                </Combobox.Control>
                <Portal>
                    <Combobox.Positioner>
                        <Combobox.Content>
                            {#if customers.length === 0}
                                <div class="p-2 text-sm text-gray-500">Keine Kunden gefunden</div>
                            {:else}
                                {#each customers as item (item.value)}
                                    <Combobox.Item item={item}>
                                        <Combobox.ItemText>{item.firstName} {item.surname}</Combobox.ItemText>
                                        <Combobox.ItemIndicator />
                                    </Combobox.Item>
                                {/each}
                            {/if}
                        </Combobox.Content>
                    </Combobox.Positioner>
                </Portal>
            </Combobox>
        </div>

        <span bind:this={scrollAnchor2}></span>
        <div class="flex justify-center py-18 border-b border-surface-200-800">
            <div class="flex flex-col gap-2 space-y-2">
                <label class="flex items-center space-x-2">
                    <input oninput={deliverySelected} class="radio" type="radio" name="delivery" value="1"/>
                    <p>Zum ausliefern</p>
                </label>

                <label class="flex items-center space-x-2">
                    <input oninput={deliverySelected} class="radio" type="radio" name="delivery" value="0"/>
                    <p>Selbstabholer</p>
                </label>
            </div>
        </div>

        <span bind:this={scrollAnchor3}></span>
        <label class="label py-18 border-b border-surface-200-800">
            <span class="label-text">Liefer-/Abholdatum</span>
            <input class="input" type="date" oninput={dateSelected} bind:this={deliveryDatePicker} bind:value={deliveryNoteForm.deliveryDate} />
        </label>

        <span bind:this={scrollAnchor4}></span>
        <div class="flex flex-col gap-4 py-18">
            <ProductSearch bind:selectedProducts={deliveryNoteForm.products} focus={productSearchFocus} />

            <div class="flex flex-col gap-2 divider-y">
                {#each deliveryNoteForm.products as product, index}
                    <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
                        <div>{product.name}</div>
                        <input class="input bg-surface-300-700 w-24" type="number" min="1" bind:value={deliveryNoteForm.products[index].quantity} />
                    </div>
                {/each}
            </div>
        </div>
    </form>
</main>