<script lang="ts">
import type { DeliveryNoteProductDto } from '$lib/formDtos/deliveryNote.svelte';
import PrintedDeliveryNoteCustomer from '$lib/components/deliveryNote/PrintedDeliveryNoteCustomer.svelte';
import PrintedDeliveryNoteInternal from '$lib/components/deliveryNote/PrintedDeliveryNoteInternal.svelte';
import PrintedReturnNote from '$lib/components/deliveryNote/PrintedReturnNote.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import TopNavigation from '$lib/components/TopNavigation.svelte';
import { fetchApi } from '$lib/fetchApi.js';
import { formatDate } from '$lib/functions/formatDate.js';
import { NotebookText, Printer, Pen, Navigation as  NavigationIcon, Mail, Phone, Truck, CalendarDays, UserGroup, PhoneOutgoing, Plus, PlusCircle, CirclePlus, Info, CircleAlert } from '@lucide/svelte';
import { Navigation } from '@skeletonlabs/skeleton-svelte';
import { tick } from 'svelte';
import ProductTable from './ProductTable.svelte';
import InfoCard from '$lib/components/InfoCard.svelte';

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

<svelte:window 
    onbeforeprint={() => {if(desiredPrint === 'deliveryNote') {printing = true;} else if(desiredPrint === 'returnNote') {printingReturnNote = true;} }} 
    onafterprint={async () => {printing = false; printingReturnNote = false}}
/>

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

    {#if data.deliveryNote.deliveryNoteProducts.find((product: DeliveryNoteProductDto) => !product.quantity)}
        <InfoCard title="Mengenangaben unvollständig" message="Bitte überprüfe die Mengenangaben der Artikel." type="warning" />
    {/if}

    <div class="customer-name bg-primary-50-950 w-full py-8 text-3xl font-bold mb-2">
        <div class="lg:max-w-[70vw] px-2 lg:px-0 mx-auto flex justify-between">
            <div class="flex items-center gap-2">
                {data.deliveryNote.customer.displayName}
            </div>

            <div class="flex items-center gap-2">
                {formatDate(data.deliveryNote.deliveryDate)}
                <CalendarDays size="32"/>
            </div>
        </div>
    </div>

    <main class="mx-auto lg:max-w-[70vw]">
        {#if data.deliveryNote.status >= 4}
            <div class="badge preset-filled-success-500 mx-2 mb-2">Zurückgeschrieben</div>
        {/if}

        <div class="flex justify-between">
            <div class="flex flex-col gap-1 border-s border-surface-400-600 ps-2">
                {#if data.deliveryNote.customer.email}
                    <div class="flex items-center gap-2">
                        <Mail size="20"/>
                        <a class="underline" href="mailto:{data.deliveryNote.customer.email}">{data.deliveryNote.customer.email}</a>
                    </div>
                {/if}

                {#if data.deliveryNote.customer.phone}
                    <div class="flex items-center gap-2">
                        <Phone size="20"/>
                        <a class="underline" href="tel:{data.deliveryNote.customer.phone}">{data.deliveryNote.customer.phone}</a>
                    </div>
                {/if}

                {#if data.deliveryNote.assignment}
                    <div class="mt-1">
                        <span class="badge preset-filled-surface-500">{data.deliveryNote.assignment}</span>
                    </div>
                {/if}

                <div class="delivery-info flex items-center gap-2">
                    <Truck size="20"/>
                    {#if data.deliveryNote.delivery}
                        Lieferung
                    {:else}
                        Selbstabholer
                    {/if}
                </div>

                {#if data.deliveryNote.adultGuests > 0 || data.deliveryNote.childGuests > 0}
                    <div class="delivery-info flex items-center gap-2">
                        <UserGroup size="20"/>
                        {#if data.deliveryNote.adultGuests > 0}
                            <span>{data.deliveryNote.adultGuests} Erwachsene</span>
                        {/if}
                        {#if data.deliveryNote.childGuests > 0}
                            -<span>{data.deliveryNote.childGuests} Kinder</span>
                        {/if}
                    </div>
                {/if}

                {#if data.deliveryNote.callBefore}
                    <div class="delivery-info flex items-center gap-2">
                        <PhoneOutgoing size="20"/>
                        <span>Vorher anrufen</span>
                    </div>
                {/if}

                {#if data.deliveryNote.pickupDate}
                    <div class="delivery-info flex items-center gap-2">
                        <CalendarDays size="20"/>
                        <span>Zurück am: {formatDate(data.deliveryNote.pickupDate)}</span>
                    </div>
                {/if}
            </div>

            {#if data.deliveryNote.shippingAddress}
                <div class="flex items-center gap-4 px-2 border-e border-surface-400-600">
                    <address>
                        {data.deliveryNote.shippingAddress.street} {data.deliveryNote.shippingAddress.houseNumber}<br />
                        {data.deliveryNote.shippingAddress.postalCode} {data.deliveryNote.shippingAddress.city}
                    </address>
                    <a class="p-2 hover:bg-surface-200-800 flex items-center rounded" href="https://www.google.com/maps/search/?api=1&query={data.deliveryNote.shippingAddress.street}+{data.deliveryNote.shippingAddress.houseNumber}+{data.deliveryNote.shippingAddress.postalCode}+{data.deliveryNote.shippingAddress.city}" target="_blank">
                        <NavigationIcon/>
                    </a>
                </div>
            {/if}
        </div>

        {#if data.deliveryNote.shortDescription}
            <div class="px-2 mt-2 text-surface-800-200" style="white-space: pre-line;">{data.deliveryNote.shortDescription}</div>
        {/if}

        {#if data.deliveryNote.privateDescription}
            <div class="px-2 mt-2 text-surface-800-200" style="white-space: pre-line;">{data.deliveryNote.privateDescription}</div>
        {/if}

        <ProductTable deliveryNote={data.deliveryNote} returnUnions={data.returnUnions} />
    </main>
{:else if printing}
    <PrintedDeliveryNoteCustomer deliveryNote={{
        id: data.deliveryNote.id,
        displayName: `${data.deliveryNote.customer.displayName}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        shippingAddress: data.deliveryNote.shippingAddress,
        status: data.deliveryNote.status,
        shortDescription: data.deliveryNote.shortDescription
    }} />
    <PrintedDeliveryNoteInternal deliveryNote={{
        id: data.deliveryNote.id,
        displayName: `${data.deliveryNote.customer.displayName}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        shippingAddress: data.deliveryNote.shippingAddress,
        billingAddress: data.deliveryNote?.billingAddress,
        status: data.deliveryNote.status,
        shortDescription: data.deliveryNote.shortDescription
    }} />
{:else if printingReturnNote}
    <PrintedReturnNote deliveryNote={{
        id: data.deliveryNote.id,
        displayName: `${data.deliveryNote.customer.displayName}`,
        delivery: data.deliveryNote.delivery,
        deliveryDate: data.deliveryNote.deliveryDate,
        deliveryNoteProducts: data.deliveryNote.deliveryNoteProducts,
        status: data.deliveryNote.status
    }} returnUnions={data.returnUnions} />
{/if}