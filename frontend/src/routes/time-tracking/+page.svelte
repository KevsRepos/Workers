<script lang="ts">
import { invalidateAll } from "$app/navigation";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { fetchApi } from "$lib/fetchApi";
import { CalendarClock, ChevronDown, ChevronRight, LayoutList, } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

const { data } = $props();

$inspect(data);

let day = $state(new Date().getDate());
let start = $state('');
let breakDuration = $state(0);
let end = $state('');

let entryInputs: Record<number, { start: string; breakDuration: number; end: string }> = $state({});

const ensureTimeSheet = async (): Promise<string | null> => {
    let timeSheetId = data.timeSheet?.id;

    if (!timeSheetId) {
        const now = new Date();
        const created = await fetchApi('time-sheets/current/create', 'POST', {
            month: now.getMonth() + 1,
            year: now.getFullYear()
        });
        timeSheetId = created.data.id;
    }

    return timeSheetId;
}

const saveEntry = async (entryDay: number, entryStart: string, entryBreakDuration: number, entryEnd: string) => {
    try {
        const timeSheetId = await ensureTimeSheet();

        await fetchApi(`time-sheets/${timeSheetId}/entries`, 'POST', {
            day: entryDay,
            start: entryStart,
            breakDuration: entryBreakDuration,
            end: entryEnd
        });

        await invalidateAll();
    } catch (e) {
        console.error(e);
    }
}

const saveToday = () => saveEntry(day, start, breakDuration, end);

const saveForDay = (dayIndex: number) => {
    const input = entryInputs[dayIndex];
    if (!input) return;
    saveEntry(dayIndex, input.start, input.breakDuration, input.end);
}

</script>

<TopNavigation>
    <Navigation.TriggerAnchor href="/delivery-note/create">
        <CalendarClock />
        <Navigation.TriggerText>Neu</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
    <Navigation.TriggerAnchor href="/time-tracking/list/{(new Date().getFullYear())}">
        <LayoutList />
        <Navigation.TriggerText>Anzeigen</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Zeiterfassung</PageHeadline>

<main class="lg:max-w-200 mx-auto">
    {#if !data.timeSheet?.entries?.[new Date().getDate() - 1]}
        <div class="flex flex-col gap-2 border-b px-2 pb-4 mb-4">
            <div>Eintrag für Heute:</div>

            <label>
                <span class="label-text">Beginn</span>
                <input class="input" type="time" placeholder="Beginn (8:00)" bind:value={start} />
            </label>
            <label>
                <span class="label-text">Pause (Minuten)</span>
                <input class="input" type="number" placeholder="Pause (60 Min)" bind:value={breakDuration} />
            </label>
            <label>
                <span class="label-text">Ende</span>
                <input class="input" type="time" placeholder="Ende (17:00)" bind:value={end} />
            </label>
            <button onclick={saveToday} type="button" class="btn preset-filled">
                Tag hinzufügen
                <ChevronDown />
            </button>
        </div>
    {/if}

    {#if data.timeSheet}
        <div class="my-2 flex justify-center">
            <div class="badge preset-filled-surface-500">Gesamtstunden: {data.timeSheet.totalHours} Std</div>
        </div>
        <div class="flex flex-col mt-4 w-full">
            {#each data.timeSheet.entries as entry, index}
                {#if entry}
                    <div style="grid-template-columns: 5% auto;" class="grid gap-2 border-b px-2">
                        <div class="border-r pr-2 font-bold">{entry.day}</div>
                        <div class="flex justify-between w-full">
                            <div>{entry.start} - {entry.end} (Pause: {entry.breakDuration} Min)</div>
                            <div>{entry.totalHours} Std</div>
                        </div>
                    </div>
                {:else if index < new Date().getDate() - 1}
                    <div style="grid-template-columns: 5% auto;" class="grid gap-2 border-b w-full h- bg-surface-100-900 px-2">
                        <div class="border-r pr-2 font-bold">{index + 1}</div>
                        <div class="flex flex-col gap-2 py-2">
                            <div class="flex gap-2">
                                <input placeholder="Beginn" type="time" style="display:inline-block; width:33.33%" oninput={(e) => { entryInputs[index + 1] = { ...entryInputs[index + 1] ?? { start: '', breakDuration: 0, end: '' }, start: e.currentTarget.value }; }} />
                                <input placeholder="Pause" type="number" style="display:inline-block; width:33.33%" oninput={(e) => { entryInputs[index + 1] = { ...entryInputs[index + 1] ?? { start: '', breakDuration: 0, end: '' }, breakDuration: Number(e.currentTarget.value) }; }} />
                                <input placeholder="Ende" type="time" style="display:inline-block; width:33.33%" oninput={(e) => { entryInputs[index + 1] = { ...entryInputs[index + 1] ?? { start: '', breakDuration: 0, end: '' }, end: e.currentTarget.value }; }} />
                            </div>
                            <button class="btn btn-sm preset-filled mb-2" onclick={() => saveForDay(index + 1)}>
                                Hinzufügen
                                <ChevronRight size="16"/>
                            </button>
                        </div>
                    </div>
                {/if}
            {/each}
        </div>
    {/if}
</main>