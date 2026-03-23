<script lang="ts">
import PrintedDeliveryNoteCustomer from '$lib/components/deliveryNote/PrintedDeliveryNoteCustomer.svelte';
import PrintedDeliveryNoteInternal from '$lib/components/deliveryNote/PrintedDeliveryNoteInternal.svelte';
import PrintedReturnNote from '$lib/components/deliveryNote/PrintedReturnNote.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import TopNavigation from '$lib/components/TopNavigation.svelte';
import { fetchApi } from '$lib/fetchApi.js';
import { formatDate } from '$lib/functions/formatDate.js';
import { NotebookText, Printer, Pen } from '@lucide/svelte';
import { Navigation } from '@skeletonlabs/skeleton-svelte';
import { tick } from 'svelte';

let { data } = $props();

let printing = $state(false);
let printingReturnNote = $state(false);

let desiredPrint: string|null = $state(null);

const printDeliveryNote = async () => {
    desiredPrint = 'deliveryNote';
    printing = true;

    await tick();

    window.print();

    await fetchApi(`delivery-note/${data.deliveryNote.id}/status/2`, 'PUT');
}

const printReturnNote = async () => {
    desiredPrint = 'returnNote';
    printingReturnNote = true;

    await tick();

    window.print();

    await fetchApi(`delivery-note/${data.deliveryNote.id}/status/5`, 'PUT');
}

$inspect(data);
</script>

<svelte:window onbeforeprint={() => {if(desiredPrint === 'deliveryNote') {printing = true;} else if(desiredPrint === 'returnNote') {printingReturnNote = true;} }} onafterprint={async () => {printing = false; printingReturnNote = false}} />

{#if !printing && !printingReturnNote}
    <TopNavigation>
        {#if data.deliveryNote.status < 4}
            <Navigation.TriggerAnchor href="/delivery-note/{data.deliveryNote.id}/edit">
                <Pen />
                <Navigation.TriggerText>Bearbeiten</Navigation.TriggerText>
            </Navigation.TriggerAnchor>
        {/if}
        <Navigation.TriggerAnchor href="/delivery-note/{data.deliveryNote.id}/return-note">
            <NotebookText />
            <Navigation.TriggerText>Zurückschreiben</Navigation.TriggerText>
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor onclick={() => printDeliveryNote()}>
            <Printer />
            <Navigation.TriggerText>Drucken</Navigation.TriggerText>
        </Navigation.TriggerAnchor>
        {#if data.deliveryNote.status >= 4}
            <Navigation.TriggerAnchor onclick={() => printReturnNote()}>
                <Printer />
                <Navigation.TriggerText>Rückschrift drucken</Navigation.TriggerText>
            </Navigation.TriggerAnchor>
        {/if}
    </TopNavigation>

    <PageHeadline>Lieferschein</PageHeadline>

    <main class="lg:max-w-200 mx-auto">
        {#if data.deliveryNote.status >= 4}
            <div class="badge preset-filled-success-500 mx-2 mb-2">Zurückgeschrieben</div>
        {/if}

        <div class="customer-name font-bold px-2">{data.deliveryNote.customer.firstName} {data.deliveryNote.customer.surname}</div>

        <div class="delivery-info px-2">
            {#if data.deliveryNote.delivery}
                Zum liefern am {formatDate(data.deliveryNote.deliveryDate)}
            {:else}
                Zum abholen am {formatDate(data.deliveryNote.deliveryDate)}
            {/if}
        </div>

        <table class="mt-4 table">
            <thead>
                <tr>
                    <th>Artikel</th>
                    <th class={{'text-right!': data.deliveryNote.status < 4, 'text-center!': data.deliveryNote.status >= 4}}>Menge</th>
                    {#if data.deliveryNote.status >= 4}
                        <th class="text-center!">Zurück</th>
                        <th class="text-center!">Gesamt</th>
                    {/if}
                </tr>
            </thead>
            <tbody class="[&>tr>td]:border-l [&>tr>td]:border-r [&>tr>td]:border-surface-200-800 border-b border-surface-200-800">
                {#each data.deliveryNote.deliveryNoteProducts as item}
                    <tr>
                        <td>{item.product.name}</td>
                        <td class={{'text-right!': data.deliveryNote.status < 4, 'text-center!': data.deliveryNote.status >= 4}}>{item.quantity} Stk.</td>

                        {#if data.deliveryNote.status >= 4}
                            <td class="text-center!">
                                {#if item.returnedFull !== null}
                                    {item.returnedFull} Stk.
                                {:else}
                                    -
                                {/if}
                                {#if item.returnedFullBottles !== null}
                                    <br />{item.returnedFullBottles} Fl.
                                {:else}
                                    <br />-
                                {/if}
                            </td>
                            <td class="text-right!">
                                {#if item.returnedTotal !== null}
                                    {#if item.product.deposit}
                                        {item.returnedTotal} * {((item.product.deposit.crateAmount + (item.product.deposit.singleAmount * item.product.quantityInCrate)) / 100).toFixed(2)}€
                                    {:else}
                                        {item.returnedTotal} Stk.
                                    {/if}
                                {:else}
                                    -
                                {/if}
                                {#if item.returnedTotalBottles !== null}
                                    {#if item.product.deposit}
                                        <br />{item.returnedTotalBottles} * {(item.product.deposit.singleAmount / 100).toFixed(2)}€
                                    {:else}
                                        <br />{item.returnedTotalBottles} Fl.
                                    {/if}
                                {:else}
                                    <br />-
                                {/if}
                            </td>
                        {/if}
                    </tr>
                {/each}
            </tbody>
        </table>
    </main>
{:else if printing}
    <PrintedDeliveryNoteCustomer deliveryNote={{
        id: data.deliveryNote.id,
        customerName: `${data.deliveryNote.customer.firstName} ${data.deliveryNote.customer.surname}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        address: '',
        status: data.deliveryNote.status
    }} />
    <PrintedDeliveryNoteInternal deliveryNote={{
        id: data.deliveryNote.id,
        customerName: `${data.deliveryNote.customer.firstName} ${data.deliveryNote.customer.surname}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        address: '',
        status: data.deliveryNote.status
    }} />
{:else if printingReturnNote}
    <PrintedReturnNote deliveryNote={{
        id: data.deliveryNote.id,
        customerName: `${data.deliveryNote.customer.firstName} ${data.deliveryNote.customer.surname}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        address: '',
        status: data.deliveryNote.status
    }} />
{/if}