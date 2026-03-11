<script>
import { NotebookText, Printer, Pen } from '@lucide/svelte';
import { Navigation } from '@skeletonlabs/skeleton-svelte';

let { data } = $props();
</script>

<Navigation class="mb-3">
    <Navigation.Menu>
        <Navigation.TriggerAnchor href="/delivery-note/create">
            <Pen />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor href="/delivery-note/create">
            <NotebookText />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor>
            <Printer />
        </Navigation.TriggerAnchor>
    </Navigation.Menu>
</Navigation>

<main>
    <h1 class="mt-2 mb-2 px-4 text-2xl font-bold">Lieferschein</h1>

    <div class="font-bold px-4">{data.deliveryNote.customer.firstName} {data.deliveryNote.customer.surname}</div>

    <div class="px-4">
        {#if data.deliveryNote.delivery}
            Zum liefern am {new Date(data.deliveryNote.deliveryDate).toLocaleDateString('de-DE')}
        {:else}
            Zum abholen am {new Date(data.deliveryNote.deliveryDate).toLocaleDateString('de-DE')}
        {/if}
    </div>

    <table class="mt-4 table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Menge</th>
            </tr>
        </thead>
        <tbody>
            {#each data.deliveryNote.deliveryNoteProducts as item}
                <tr>
                    <td>{item.product.name}</td>
                    <td class="text-right">{item.quantity}</td>
                </tr>
            {/each}
        </tbody>
    </table>
</main>