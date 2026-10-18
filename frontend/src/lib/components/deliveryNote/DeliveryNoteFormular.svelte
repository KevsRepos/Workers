<script lang="ts">
import { parseDate, Steps } from "@skeletonlabs/skeleton-svelte";
import CustomerSearch from "./CustomerSearch.svelte";
import ProductSearch from "./ProductSearch.svelte";
import { onMount } from "svelte";
import { isDeliveryNoteSubmittable, normalizePickupDate } from "$lib/deliveryNote/validation";
import { formatDate } from "$lib/functions/formatDate";
import OrderDescriptions from "./OrderDescriptions.svelte";
import DeliveryDetails from "./DeliveryDetails.svelte";
import Summary from "./Summary.svelte";

let { deliveryNoteForm, saveDeliveryNote, removedProductIds = [] } = $props();

let step = $state(0);

let deliveryNoteIsValid = $derived(isDeliveryNoteSubmittable({
    customer: deliveryNoteForm.customer,
    deliveryDate: deliveryNoteForm.deliveryDate,
    delivery: deliveryNoteForm.delivery,
    products: deliveryNoteForm.products,
}));

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
    { title: 'Zusammenfassung'}
];

const setDeliveryNoteDate = (date: string) => {
    deliveryNoteForm.deliveryDate = date;
    deliveryNoteForm.pickupDate = normalizePickupDate(deliveryNoteForm.deliveryDate, deliveryNoteForm.pickupDate);
}
</script>

<main class="px-4 flex flex-col h-full">
    <Steps {step} onStepChange={(details) => (step = details.step)} orientation="vertical" count={steps.length} class="h-full flex-1 flex gap-8" linear={false} defaultStep={2}>
        <Steps.List class="hidden sm:flex h-fit">
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
                <CustomerSearch bind:selectedCustomer={deliveryNoteForm.customer} bind:selectedShippingAddress={deliveryNoteForm.shippingAddressId} bind:selectedBillingAddress={deliveryNoteForm.billingAddressId} />
            </Steps.Content>

            <Steps.Content index={1}>
                <OrderDescriptions {deliveryNoteForm} />
            </Steps.Content>

            <Steps.Content index={2}>
                <DeliveryDetails {deliveryNoteForm} {setDeliveryNoteDate} {parseDate} />
            </Steps.Content>

            <Steps.Content index={3}>
                <ProductSearch deliveryNoteForm={deliveryNoteForm} selectedProducts={deliveryNoteForm.products} removedProductIds={removedProductIds} />
            </Steps.Content>

            <Steps.Content index={4}>
                <Summary deliveryNoteForm={deliveryNoteForm} {step} {saveDeliveryNote} {deliveryNoteIsValid} {formatDate} />
            </Steps.Content>

            <div class="flex justify-between gap-2 w-full">
                <Steps.PrevTrigger class="btn preset-filled">Zurück</Steps.PrevTrigger>

                {#if step !== steps.length - 1}
                    <Steps.NextTrigger class="btn preset-filled">Weiter</Steps.NextTrigger>
                {/if}
            </div>
        </div>
    </Steps>
</main>