<script lang="ts">
import { Calendar1, Car, NotebookPen, Printer, User, Van } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { formatDate } from "$lib/functions/formatDate.js";
import { fetchApi } from "$lib/fetchApi.js";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { tick } from "svelte";
import { goto } from "$app/navigation";

let { data } = $props();

let deliveryNotes = $derived(data.deliveryNotes);

const filterOptions = [
    { value: '1', label: 'Offen' },
    { value: '2', label: 'Ausgeliefert' },
    { value: '4', label: 'Zurückgeschrieben' },
    { value: '5', label: 'Abgeschlossen' },
    { value: '3', label: 'Storniert' },
];

let filter = $state(data.filter);

const onFilterChange = () => {
    goto(`/delivery-note/list/${filter}`);
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
        <select bind:value={filter} onchange={onFilterChange}>
            {#each filterOptions as opt}
                <option value={opt.value}>{opt.label}</option>
            {/each}
        </select>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Lieferscheine</PageHeadline>

<main class="lg:max-w-200 mx-auto">
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