<script>
import { NotebookPen, Printer } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

let { data } = $props();
</script>

<Navigation class="mb-3">
    <Navigation.Menu>
        <Navigation.TriggerAnchor href="/delivery-note/create">
            <NotebookPen />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor>
            <Printer />
        </Navigation.TriggerAnchor>
    </Navigation.Menu>
</Navigation>

<main class="px-4">
    <h1 class="mb-2 text-center font-bold">Lieferscheine</h1>

    <div class="flex flex-col gap-2">
        {#each data.deliveryNotes as deliveryNote}
            <a href="/delivery-note/{deliveryNote.id}" class="bg-surface-200-800 flex row justify-between p-2 w-full shadow-sm font-bold">
                <div>{deliveryNote.customer.firstName} {deliveryNote.customer.surname}</div>
                <div>{new Date(deliveryNote.deliveryDate).toLocaleDateString('de-DE')}</div>
            </a>
        {/each}
    </div>
</main>