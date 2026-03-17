<script lang="ts">
import { Calendar1, Car, NotebookPen, Printer, User, Van } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { formatDate } from "$lib/functions/formatDate.js";
import { fetchApi } from "$lib/fetchApi.js";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { tick } from "svelte";

let { data } = $props();

let deliveryNotes = $state(data.deliveryNotes);

let status: string = $state("1");


let dererror = $state("");

const filterStatus = async () => {
    await tick();

    // dererror = 'looooool';

    try {
        const json = await fetchApi(`delivery-notes/${status}`, "GET");

        deliveryNotes = json;
    } catch (error) {
        dererror = `Fehler beim Laden der Lieferscheine: ${error.message}`;
    }
}
</script>

<TopNavigation>
    <Navigation.TriggerAnchor href="/delivery-note/create">
        <NotebookPen />
        <Navigation.TriggerText>Erstellen</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
    <Navigation.TriggerAnchor>
        <Printer />
        <Navigation.TriggerText>Alle drucken</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
    <Navigation.TriggerAnchor>
        <select bind:value={status} onchange={filterStatus}>
            <option value="1">Offen</option>
            <option value="2">Ausgeliefert</option>
            <option value="4">Zurückgeschrieben</option>
            <option value="5">Abgeschlossen</option>
            <option value="3">Storniert</option>
        </select>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Lieferscheine</PageHeadline>

<main>
    <div class="flex flex-col">
        {#each deliveryNotes as deliveryNote}
            <a href="/delivery-note/{deliveryNote.id}" class="flex flex-col gap-4 p-2 w-full not-last:border-b">
                <div class="flex justify-between">
                    <div class="flex gap-2 font-bold">
                        <User />
                        {deliveryNote.customer.firstName} {deliveryNote.customer.surname}
                    </div>
                    <div class="flex items-center gap-2 font-bold">
                        <div>{formatDate(deliveryNote.deliveryDate)}</div>
                        <Calendar1 />
                    </div>
                </div>
                <div class="flex gap-2">
                    {#if deliveryNote.delivery}
                        <Van />
                        Zum liefern
                    {:else}
                        <Car />
                        Zum abholen
                    {/if}
                </div>
            </a>
        {:else}
            <div class="text-center text-sm text-muted-foreground">Keine Lieferscheine gefunden.</div>
        {/each}
    </div>
</main>