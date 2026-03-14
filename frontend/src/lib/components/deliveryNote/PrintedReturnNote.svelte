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
                <th class="font-bold text-center!">Menge</th>
                <th class="text-center!">Zurück</th>
                <th class="text-center!">Gesamt</th>
            </tr>
        </thead>
        <tbody class="[&>tr>td]:py-0 [&>tr>td]:border-l-[0.06cm] [&>tr>td]:border-r-[0.06cm] [&>tr>td]:border-surface-800 border-b border-surface-800">
            {#each deliveryNote.deliveryNoteProducts as item}
                <tr>
                    <td>{item.product.name}</td>
                    <td class="text-center!">{item.quantity} Stk.</td>

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
                                {item.returnedTotal} * {(((item.product.deposit.crateAmount || 0) + (item.product.deposit.singleAmount * (item.product.quantityInCrate || 0))) / 100).toFixed(2)}€
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
        /* margin: 5mm; */
    }
    .page {
        height: 100vh;
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