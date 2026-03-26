<script lang="ts">
import { formatDate } from "$lib/functions/formatDate";
import type { DeliveryNote, ReturnUnion } from "$lib/interfaces/DeliveryNote";

const { deliveryNote, returnUnions }: { deliveryNote: DeliveryNote, returnUnions: ReturnUnion[] } = $props();
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
                <th class="font-bold text-center!">Menge</th>
                <th class="text-center!">Zurück</th>
                <th class="text-center!">Gesamt</th>
            </tr>
        </thead>
        <tbody class="[&>tr>td]:py-0 [&>tr>td]:border-l-[0.06cm] [&>tr>td]:border-r-[0.06cm] [&>tr>td]:border-surface-800 border-b border-surface-800">
            {#each returnUnions as union}
                <tr>
                    <td>
                        {union.name}
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
                        {:else}
                            -
                        {/if}
                        {#if union.returnNoteEntry?.returnedTotalBottles}
                            {#if union.deposit}
                                <br />{union.returnNoteEntry.returnedTotalBottles} * {(union.deposit.singleAmount / 100).toFixed(2)}€
                            {:else}
                                <br />{union.returnNoteEntry.returnedTotalBottles} Fl.
                            {/if}
                        {/if}
                    </td>
                    
                    <!-- Empty Cell for handwritten notes -->
                    <td class="w-20"></td>
                </tr>
            {/each}
        </tbody>
    </table>
</div>

<style>
@media print {
    @page {
        size: A4;
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