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

    if(data.deliveryNote.status < 2) {
        await fetchApi(`delivery-note/${data.deliveryNote.id}/status/2`, 'PUT');
    }
}

const printReturnNote = async () => {
    desiredPrint = 'returnNote';
    printingReturnNote = true;

    await tick();

    window.print();

    if(data.deliveryNote.status < 5) {
        await fetchApi(`delivery-note/${data.deliveryNote.id}/status/5`, 'PUT');
    }
}
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
                {#if data.deliveryNote.status >= 4}
                    {#each data.returnUnions as union}
                        <tr>
                            <td>
                                {union.name}
                                {#if union.isUnion}
                                    <span class="text-surface-500 text-sm">(Zusammengefasste Einheit)</span>
                                {/if}
                            </td>
                            <td class="text-center!">{union.quantity} Stk.</td>
                            <td class="text-center!">
                                {#if union.returnNoteEntry?.returnedFull}
                                    {union.returnNoteEntry.returnedFull} Stk.
                                {/if}
                                {#if union.returnNoteEntry?.returnedFullBottles}
                                    <br />{union.returnNoteEntry.returnedFullBottles} Fl.
                                {/if}
                            </td>
                            <td class="text-right!">
                                {#if union.returnNoteEntry?.returnedTotal}
                                    {#if union.deposit}
                                        {union.returnNoteEntry.returnedTotal} * {(((union.deposit.crateAmount || 0) + (union.deposit.singleAmount * (union.quantityInCrate || 0))) / 100).toFixed(2)}€
                                    {:else}
                                        {union.returnNoteEntry.returnedTotal} Stk.
                                    {/if}
                                {/if}
                                {#if union.returnNoteEntry?.returnedTotalBottles}
                                    {#if union.deposit}
                                        <br />{union.returnNoteEntry.returnedTotalBottles} * {(union.deposit.singleAmount / 100).toFixed(2)}€
                                    {:else}
                                        <br />{union.returnNoteEntry.returnedTotalBottles} Fl.
                                    {/if}
                                {/if}
                            </td>
                        </tr>
                    {/each}
                {:else}
                    {#each data.deliveryNote.deliveryNoteProducts as item}
                        <tr>
                            <td>{item.product.name}</td>
                            <td class="text-right!">{item.quantity} Stk.</td>
                        </tr>
                    {/each}
                {/if}
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
    }} returnUnions={data.returnUnions} />
{/if}