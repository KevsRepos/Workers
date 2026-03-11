<script lang="ts">
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";
import ProductSearch from "./ProductSearch.svelte";
import { ChevronRight, CircleX } from "@lucide/svelte";
import CustomerSearch from "./CustomerSearch.svelte";
import { fetchApi } from "$lib/fetchApi";
import { goto } from "$app/navigation";

let { deliveryNoteForm, saveDeliveryNote } = $props();

let scrollAnchor2 = $state<HTMLSpanElement>();
let scrollAnchor3 = $state<HTMLSpanElement>();
let scrollAnchor4 = $state<HTMLSpanElement>();

let deliveryDatePicker = $state<HTMLInputElement>();

const focusProductSearch = () => {
    scrollAnchor4?.scrollIntoView({ behavior: 'smooth' });
    (document.querySelector('#product-search-input') as HTMLInputElement)?.focus();
}

const focusDeliverySelection = () => {
    scrollAnchor2?.scrollIntoView({ behavior: 'smooth' });
}

const deliverySelected = () => {
    scrollAnchor3?.scrollIntoView({ behavior: 'smooth' });

    deliveryDatePicker?.focus();
    deliveryDatePicker?.showPicker();
}

const dateSelected = () => {
    focusProductSearch();
}

$inspect(deliveryNoteForm);
</script>

<main class="px-4" style="min-height: 4000px">
    <h1 class="mt-4 mb-2 text-center font-bold">Lieferschein erstellen</h1>

    <div class="flex flex-col gap-2">
        <div class="pt-9 pb-18 border-b border-surface-200-800">
            <CustomerSearch bind:selectedCustomer={deliveryNoteForm.customer} jump={focusDeliverySelection} />
        </div>

        <span bind:this={scrollAnchor2}></span>
        <div class="flex justify-center py-18 border-b border-surface-200-800">
            <div class="flex flex-col gap-2 space-y-2">
                <label class="flex items-center space-x-2">
                    <input oninput={deliverySelected} bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={true}/>
                    <p>Zum ausliefern</p>
                </label>

                <label class="flex items-center space-x-2">
                    <input oninput={deliverySelected} bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={false}/>
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
            <ProductSearch deliveryNoteForm={deliveryNoteForm} selectedProducts={deliveryNoteForm.products} jump={focusProductSearch} />

            <div class="flex flex-col gap-2 divider-y">
                {#each deliveryNoteForm.products as product, index}
                    <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
                        <button onclick={() => deliveryNoteForm.products.splice(index, 1)} type="button">
                            <CircleX />
                        </button>
                        <div>{product.name}</div>
                        <input class="input bg-surface-300-700 w-24" type="number" min="1" bind:value={deliveryNoteForm.products[index].quantity} />
                    </div>
                {/each}
            </div>
        </div>

        <button onclick={saveDeliveryNote} type="button" class="btn preset-filled">
            <span>Speichern</span>
            <ChevronRight />
        </button>
    </div>
</main>