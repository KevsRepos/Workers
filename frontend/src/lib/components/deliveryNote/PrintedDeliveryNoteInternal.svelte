<script lang="ts">
import { formatDate } from "$lib/functions/formatDate";
import type { DeliveryNote } from "$lib/interfaces/DeliveryNote";

const { deliveryNote }: { deliveryNote: DeliveryNote } = $props();
</script>

<div class="page">
    <div class="font-bold mb-2">{deliveryNote.customerName}</div>

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
</div>

<style>
@media print {
    @page {
        size: A4;
        margin: 20mm;
            .page {
                height: auto !important;
                min-height: 0 !important;
                margin-top: 0 !important;
            }
        padding: 0;
        height: auto !important;
    }

    .page {
        height: auto !important;
        min-height: 0 !important;
        margin-top: 0 !important;
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
}
</style>