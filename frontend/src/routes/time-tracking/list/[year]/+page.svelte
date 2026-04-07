<script lang="ts">
import { goto } from "$app/navigation";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { CalendarClock } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

let { data } = $props();

const monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

let filter = $state(data.year);

const onFilterChange = () => {
    console.log(filter);
    
    goto(`/time-tracking/list/${filter}`);
}

$inspect(data);
</script>

<TopNavigation>
    <Navigation.TriggerAnchor href="/time-tracking">
        <CalendarClock />
        <Navigation.TriggerText>Aktuell</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
    <Navigation.TriggerAnchor>
        <select bind:value={filter} onchange={onFilterChange}>
            {#each data.years as year}
                <option value={year.toString()}>{year}</option>
            {/each}
        </select>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Zeiterfassung {data.year}</PageHeadline>

<main class="lg:max-w-200 mx-auto">
    <div class="flex flex-col">
        {#each data.timeSheets as sheet}
            <a href="/time-tracking/list/{data.year}/{sheet.month}" class="flex justify-between items-center p-3 not-last:border-b">
                <div class="font-bold">{monthNames[sheet.month - 1]}</div>
            </a>
        {:else}
            <div class="text-center text-sm text-surface-500 mt-4">Keine Einträge für {data.year}.</div>
        {/each}
    </div>
</main>