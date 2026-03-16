<script lang="ts">
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";
import ProductSearch from "./ProductSearch.svelte";
import { ChevronRight, CircleX } from "@lucide/svelte";
import CustomerSearch from "./CustomerSearch.svelte";
import { fetchApi } from "$lib/fetchApi";
import { goto } from "$app/navigation";
import { onMount } from "svelte";


let { deliveryNoteForm, saveDeliveryNote, removedProductIds = [] } = $props();
// Helper to format date as yyyy-MM-dd for input[type=date]
const formatDateForInput = (dateStr: string): string => {
    if (!dateStr) return '';
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '';
    return d.toISOString().slice(0, 10);
}

let deliveryDate = $state(formatDateForInput(deliveryNoteForm.deliveryDate));

onMount(() => {
    if(deliveryNoteForm.customer === null || deliveryNoteForm.delivery === undefined) {
        deliveryNoteForm.delivery = null;
    }
});
$effect(() => {
    if (deliveryNoteForm.deliveryDate !== deliveryDate) {
        deliveryNoteForm.deliveryDate = deliveryDate;
    }
});

let scrollAnchor2 = $state<HTMLSpanElement>();
let scrollAnchor3 = $state<HTMLSpanElement>();
let scrollAnchor4 = $state<HTMLSpanElement>();

let deliveryDatePicker = $state<HTMLInputElement>();

$inspect(deliveryNoteForm.deliveryDate);

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

const removeProduct = (index: number) => {
    const removed = deliveryNoteForm.products.splice(index, 1);

    if (removed[0].id) {
        removedProductIds.push(removed[0].id);
    }
}
</script>

<main class="lg:max-w-200 mx-auto">
    <div class="flex flex-col gap-2">
        <div class="pt-2 pb-9 border-b border-surface-200-800">
            <CustomerSearch autoFocus={deliveryNoteForm.customer === null} bind:selectedCustomer={deliveryNoteForm.customer} jump={focusDeliverySelection} />
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
        <div class="border-b border-surface-200-800">
            <label class="label pb-9 px-4">
                <span class="label-text">Liefer-/Abholdatum</span>
                <input class="input" type="date" oninput={dateSelected} bind:this={deliveryDatePicker} bind:value={deliveryDate} />
            </label>
        </div>

        <span bind:this={scrollAnchor4}></span>
        <div class="flex flex-col gap-4 pb-9">
            <div class="px-4">
                <ProductSearch deliveryNoteForm={deliveryNoteForm} selectedProducts={deliveryNoteForm.products} jump={focusProductSearch} />

                <div class="flex flex-col gap-2 mt-2 divider-y max-h-64 overflow-y-auto">
                    {#each deliveryNoteForm.products as product, index}
                        <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
                            <button onclick={() => removeProduct(index)} type="button">
                                <CircleX />
                            </button>
                            <div>{product.name}</div>
                            <input class="input bg-surface-300-700 w-24" type="number" min="1" bind:value={deliveryNoteForm.products[index].quantity} />
                        </div>
                    {/each}
                </div>
            </div>
        </div>

        {#if deliveryNoteForm.products.length > 0 && deliveryNoteForm.customer && deliveryNoteForm.delivery !== null && deliveryNoteForm.deliveryDate}
            <button onclick={saveDeliveryNote} type="button" class="btn mx-2 py-4 preset-filled mb-150">
                <span>Speichern</span>
                <ChevronRight />
            </button>
        {/if}
    </div>
</main>