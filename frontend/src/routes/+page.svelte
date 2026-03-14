<script lang="ts">
import { NotebookPen, Printer } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";
import Page from "./delivery-note/[id]/+page.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { formatDate } from "$lib/functions/formatDate.js";
import { fetchApi } from "$lib/fetchApi.js";
import { onMount, tick } from "svelte";

let { data } = $props();

let deliveryNotes = $state(data.deliveryNotes);

let status: string = $state("1");

const filterStatus = async () => {
    await tick();

    const json = await fetchApi(`delivery-notes/${status}`, "GET");

    deliveryNotes = json;
}
</script>

<Navigation class="mb-3">
    <Navigation.Menu>
        <Navigation.TriggerAnchor href="/delivery-note/create">
            <NotebookPen />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor>
            <Printer />
        </Navigation.TriggerAnchor>
        <Navigation.TriggerAnchor>
            <select bind:value={status} onchange={filterStatus}>
                <option value="1">Offen</option>
                <option value="2">Ausgeliefert</option>
                <option value="5">Zurückgeschrieben</option>
                <option value="3">Storniert</option>
            </select>
        </Navigation.TriggerAnchor>
    </Navigation.Menu>
</Navigation>

<PageHeadline>Lieferscheine</PageHeadline>

<main class="p-4">
    <div class="flex flex-col gap-2">
        {#each deliveryNotes as deliveryNote}
            <a href="/delivery-note/{deliveryNote.id}" class="bg-surface-200-800 flex row justify-between p-2 w-full shadow-sm font-bold rounded">
                <div>{deliveryNote.customer.firstName} {deliveryNote.customer.surname}</div>
                <div>
                   { formatDate(deliveryNote.deliveryDate) }
                </div>
            </a>
        {:else}
            <div class="text-center text-sm text-muted-foreground">Keine Lieferscheine gefunden.</div>
        {/each}
    </div>
</main>