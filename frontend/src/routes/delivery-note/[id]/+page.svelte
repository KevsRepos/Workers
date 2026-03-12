<script>
import { NotebookText, Printer, Pen } from '@lucide/svelte';
import { Navigation } from '@skeletonlabs/skeleton-svelte';

let { data } = $props();
</script>

<Navigation class="mb-3">
    <Navigation.Menu>
        <Navigation.TriggerAnchor href="/delivery-note/{data.deliveryNote.id}/edit">
            <Pen />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor href="/delivery-note/{data.deliveryNote.id}/return-note">
            <NotebookText />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor onclick={() => print()}>
            <Printer />
        </Navigation.TriggerAnchor>
    </Navigation.Menu>
</Navigation>

<main>
    <h1 class="mt-2 mb-2 px-4 text-2xl font-bold">Lieferschein</h1>

    <div class="customer-name font-bold px-4">{data.deliveryNote.customer.firstName} {data.deliveryNote.customer.surname}</div>

    <div class="delivery-info px-4">
        {#if data.deliveryNote.delivery}
            Zum liefern am {new Date(data.deliveryNote.deliveryDate).toLocaleDateString('de-DE')}
        {:else}
            Zum abholen am {new Date(data.deliveryNote.deliveryDate).toLocaleDateString('de-DE')}
        {/if}
    </div>

    <table class="mt-4 table">
        <thead>
            <tr>
                <th>Artikel</th>
                <th class="text-right!">Menge</th>
            </tr>
        </thead>
        <tbody>
            {#each data.deliveryNote.deliveryNoteProducts as item}
                <tr>
                    <td>{item.product.name}</td>
                    <td class="text-right">{item.quantity} Stk.</td>
                </tr>
            {/each}
        </tbody>
    </table>

    <p class="print-only text-center mt-10 w-full">Alle gelieferten Waren bleiben bis zur Bezahlung Eigentum des Lieferanten.</p>
</main>

<style>
.print-only {
    display: none;
}
@page {
    size: A4;
    margin: 20mm;
}
@media print {
    .print-only {
        display: block;
    }
    :global(header) {
        display: none;
    }
    :global([data-scope="navigation"]) {
        display: none;
    }
    tr {
        border-bottom: .05cm rgb(48, 48, 48) solid;
    }
    h1, .customer-name, .delivery-info {
        padding: 0px;
    }
}
</style>