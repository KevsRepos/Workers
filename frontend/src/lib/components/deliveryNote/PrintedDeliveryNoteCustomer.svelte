<script lang="ts">
import { formatDate } from "$lib/functions/formatDate";
import type { DeliveryNote } from "$lib/interfaces/DeliveryNote";
import { company } from '$lib/config/company.js';

const { deliveryNote }: { deliveryNote: DeliveryNote } = $props();

$inspect(deliveryNote);
</script>

<div class="page">

    <div class="flex justify-end">
        <img class="logo" src="/img/logo.png" alt="Logo" />
    </div>

    <div class="flex justify-between items-start" style="margin-bottom: 0.8cm;">
        <div class="left-side">
            <div class="text-sm border-b-2 w-fit" style="margin-bottom: 0.5cm;">{company.name} — {company.address} — {company.postalCode} {company.city}</div>
            <div class="font-bold">{deliveryNote.customerName}</div>
            <div style="white-space: pre-line;">{deliveryNote.shortDescription}</div>
        </div>

        <div class="right-side">        
            <div class="text-sm text-right" style="margin-bottom: 0.5cm;">
                {company.name}<br />
                {company.address}<br />
                {company.postalCode} {company.city}
            </div>

            <div class="text-sm text-right">
                Tel.: {company.telephone}<br />
                Fax: {company.fax}<br />
                E-Mail: {company.email}
            </div>
        </div>
    </div>

    <h1 class="text-2xl font-bold mb-4">Lieferschein</h1>

    <div>
        {#if deliveryNote.delivery}
            Zum liefern am {formatDate(deliveryNote.deliveryDate)}
        {:else}
            Zum abholen am {formatDate(deliveryNote.deliveryDate)}
        {/if}
    </div>

    <table class="table">
        <thead>
            <tr class="font-bold">
                <th class="font-bold">Artikel</th>
                <th class="font-bold text-right!">Menge</th>
            </tr>
        </thead>
        <tbody>
            {#each deliveryNote.deliveryNoteProducts as product}
                <tr class="border-gray-950">
                    <td>{product.product.name}</td>
                    <td class="text-right">{product.quantity} Stk.</td>
                </tr>
            {/each}
        </tbody>
    </table>

    <p class="text-sm text-center mt-10 w-full">Alle gelieferten Waren bleiben bis zur Bezahlung Eigentum des Lieferanten.</p>
</div>

<style>
@media print {
    @page :first {
        size: A4;
        margin: 20mm;
    }
    .page {
        min-height: 100vh;
        break-after: page;
    }
    :global(header) {
        display: none;
    }
    :global([data-scope="navigation"]) {
        display: none;
    }
    tr {
        border-bottom: 0.06cm rgb(48, 48, 48) solid;
    }
    tbody td {
        padding-block: 0.1cm;
    }
    .logo {
        width: 5.5cm;
        height: auto;
        margin-bottom: .8cm;
    }
    .left-side {
        width: 10.5cm;
    }
    .right-side {
        width: 5.5cm;
    }
    tbody {
        font-size: 0.8em;
    }
}
</style>