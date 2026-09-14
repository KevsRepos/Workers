<script lang="ts">
import { Steps } from "@skeletonlabs/skeleton-svelte";
import CustomerSearch from "./CustomerSearch.svelte";
import DatePicker from "../DatePicker.svelte";
import ProductSearch from "./ProductSearch.svelte";
import { CircleX } from "@lucide/svelte";
import { onMount } from "svelte";
import { formatDate } from "$lib/functions/formatDate";

let { deliveryNoteForm, saveDeliveryNote, removedProductIds = [] } = $props();

let step = $state(0);

let deliveryNoteIsValid = $derived(!!deliveryNoteForm.customer && deliveryNoteForm.deliveryDate !== null && deliveryNoteForm.delivery !== null && deliveryNoteForm.products.length > 0);

onMount(() => {
    if(deliveryNoteForm.customer === null || deliveryNoteForm.delivery === undefined) {
        deliveryNoteForm.delivery = null;
    }
});

const steps = [
    { title: 'Kundendaten'},
    { title: 'Beschreibungen'},
    { title: 'Lieferdetails'},
    { title: 'Artikel'},
];

const removeProduct = (index: number) => {
    const removed = deliveryNoteForm.products.splice(index, 1);

    if (removed[0].id) {
        removedProductIds.push(removed[0].id);
    }
}
</script>

<main class="px-4 flex flex-col h-full">
    <Steps {step} onStepChange={(details) => (step = details.step)} orientation="vertical" count={steps.length} class="h-full flex-1 flex gap-8" linear={false} defaultStep={2}>
        <Steps.List class="hidden sm:flex h-fit px-4">
            {#each steps as step, index}
                <Steps.Item index={index}>
                    <div class="flex items-center gap-2">
                    <Steps.Trigger>
                        <Steps.Indicator>
                            {index + 1}
                        </Steps.Indicator>
                        {step.title}
                    </Steps.Trigger>
                    </div>

                    {#if index < steps.length - 1}
                        <Steps.Separator />
                    {/if}
                </Steps.Item>
            {/each}
        </Steps.List>
        
        <div class="flex flex-col justify-between gap-4 w-full bottom-0 bg-surface-100-900 p-2 rounded-md">
            <Steps.Content index={0}>
                <CustomerSearch autoFocus={deliveryNoteForm.customer === null} bind:selectedCustomer={deliveryNoteForm.customer} />
            </Steps.Content>

            <Steps.Content index={1}>
                <label class="">
                    <span class="label-text">Kurzbeschreibung</span>
                    <textarea
                        class="textarea bg-surface-50-950"
                        rows="5"
                        placeholder="Optionale Kurzbeschreibung"
                        bind:value={deliveryNoteForm.shortDescription}
                    ></textarea>
                </label>
            </Steps.Content>

            <Steps.Content index={2}>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center justify-center h-20 bg-surface-50-950 rounded-2xl gap-2">
                        <input bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={true}/>
                        <p>Zum ausliefern</p>
                    </label>

                    <label class="flex h-20 bg-surface-50-950 rounded-2xl items-center justify-center gap-2">
                        <input bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={false}/>
                        <p>Selbstabholer</p>
                    </label>
                </div>

                <DatePicker class="mt-4" label="Lieferdatum" bind:value={deliveryNoteForm.deliveryDate}></DatePicker>
            </Steps.Content>

            <Steps.Content index={3}>
                <ProductSearch deliveryNoteForm={deliveryNoteForm} selectedProducts={deliveryNoteForm.products} />

                <div class="flex flex-col gap-2 mt-2 divider-y max-h-64 overflow-y-auto">
                    {#each deliveryNoteForm.products as product, index}
                        <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
                            <div class="flex gap-2 items-center">
                                <button onclick={() => removeProduct(index)} type="button">
                                    <CircleX />
                                </button>
                                <div>{product.name}</div>
                            </div>

                            <input class="input bg-surface-300-700 w-24" type="number" min="1" bind:value={deliveryNoteForm.products[index].quantity} />
                        </div>
                    {/each}
                </div>
            </Steps.Content>

            <Steps.Content index={4}>
                <div class="border-b py-2">
                    <strong>Kunde:</strong>
                    {#if deliveryNoteForm.customer}
                        {deliveryNoteForm.customer.firstName} {deliveryNoteForm.customer.surname}<br />
                    {:else}
                        <button onclick={() => step = 0} class="btn preset-outlined ml-2">Setzen</button>
                    {/if}
                </div>

                <div class="border-b py-2">
                    <strong>Kurzbeschreibung:</strong>
                    {#if deliveryNoteForm.shortDescription}
                        {deliveryNoteForm.shortDescription}<br />
                    {:else}
                        <button onclick={() => step = 1} class="btn preset-outlined ml-2">Setzen</button>
                    {/if}
                </div>

                <div class="border-b py-2">
                    <strong>Lieferung:</strong>
                    {#if deliveryNoteForm.delivery !== null}
                        {deliveryNoteForm.delivery ? 'Zum ausliefern' : 'Selbstabholer'}<br />
                    {:else}
                        <button onclick={() => step = 2} class="btn preset-outlined ml-2">Setzen</button>
                    {/if}
                </div>

                 <div class="border-b py-2">
                    <strong>Lieferdatum:</strong>
                    {#if deliveryNoteForm.deliveryDate}
                        {formatDate(deliveryNoteForm.deliveryDate)}<br />
                    {:else}
                        <button onclick={() => step = 2} class="btn preset-outlined ml-2">Setzen</button>
                    {/if}
                </div>
                
                <div class="border-b py-2">
                    <strong>Artikel:</strong>
                    {#if deliveryNoteForm.products.length > 0}
                        <ul>
                            {#each deliveryNoteForm.products as product}
                                <li>{product.name} - {product.quantity} Stk.</li>
                            {/each}
                        </ul>
                    {:else}
                        <button onclick={() => step = 3} class="btn preset-outlined ml-2">Setzen</button>
                    {/if}
                </div>

                <button class="btn preset-filled-surface-950-50 w-full mt-4" disabled={!deliveryNoteIsValid} onclick={() => {saveDeliveryNote()}}>Speichern</button>
                
            </Steps.Content>

            <div class="flex justify-between gap-2 w-full">
                <Steps.PrevTrigger class="btn preset-filled">Zurück</Steps.PrevTrigger>
                <Steps.NextTrigger class="btn preset-filled">Weiter</Steps.NextTrigger>
            </div>
        </div>
    </Steps>
</main>