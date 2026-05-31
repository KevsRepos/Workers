<script lang="ts">
import { invalidateAll } from "$app/navigation";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import SlidingOverlay from "$lib/components/SlidingOverlay.svelte";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { fetchApi } from "$lib/fetchApi";
import { Bell, CalendarClock, ChevronDown, ChevronRight, LayoutList, Pen, } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

const { data } = $props();

let day = $state(new Date().getDate());
let start = $state('');
let breakDuration = $state(0);
let end = $state('');

let editingEntryId: string | null = $state(null);



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

        newEntry = false;
        dayIndex = null;
        start = '';
        breakDuration = 0;
        end = '';
    } catch (e) {
        console.error(e);
    }
}

const saveToday = () => saveEntry(day, start, breakDuration, end);

const saveForDay = (dayIndex: number | null) => {    
    if (dayIndex === null) return;
    
    saveEntry(dayIndex, start, breakDuration, end);
}

const updateForDay = async (dayIndex: number, entry: { id: string; start: string; breakDuration: number; end: string }) => {
    try {

        console.log(dayIndex, entry);

        await fetchApi(`time-sheets/entries/${entry.id}`, 'PUT', {
            day: dayIndex,
            start: entry.start,
            breakDuration: entry.breakDuration,
            end: entry.end
        });



        editingEntryId = null;
        start = '';
        breakDuration = 0;
        end = '';

        await invalidateAll();
    } catch (e) {
        console.error(e);
    }
}

const editEntry = (entryId: string) => {
    const entry = data.timeSheet.entries.find((e: any) => e ? e.id === entryId : false);

    if (entry) {
        start = entry.start;
        breakDuration = entry.breakDuration;
        end = entry.end;
    }

    editingEntryId = entryId;
}

let newEntry = $state(false);

let dayIndex: number | null = $state(null);

const addNew = () => {
    newEntry = true;
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
    <Navigation.TriggerAnchor href="/time-tracking/notifications">
        <Bell />
        <Navigation.TriggerText>Benachrichtigungen</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Zeiterfassung</PageHeadline>

<main class="lg:max-w-200 mx-auto">
    {#if !data.timeSheet?.entries?.[new Date().getDate() - 1]}
        <button class="btn preset-filled flex m-auto" onclick={addNew}>
            Für heute hinzufügen
            <ChevronRight />
        </button>
    {/if}

    {#if newEntry}
        <SlidingOverlay close={() => newEntry = false}>
             {#snippet title()}
                Neuer Eintrag Heute
             {/snippet}

             {#snippet body()}
                <div class="flex flex-col gap-2 border-b pb-4 mb-4">
                    <label>
                        <span class="label-text">Beginn</span>
                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Beginn (8:00)" bind:value={start} />
                    </label>
                    <label>
                        <span class="label-text">Pause (Minuten)</span>
                        <input class="input bg-surface-50" type="number" placeholder="Pause (60 Min)" bind:value={breakDuration} />
                    </label>
                    <label>
                        <span class="label-text">Ende</span>
                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Ende (17:00)" bind:value={end} />
                    </label>
                    <button onclick={saveToday} type="button" class="btn preset-filled">
                        Tag hinzufügen
                        <ChevronDown />
                    </button>
                </div>
             {/snippet}
        </SlidingOverlay>
    {/if}

    {#if data.timeSheet}
        <div class="my-2 flex justify-center">
            <div class="badge preset-filled-surface-500">Gesamtstunden: {data.timeSheet.totalHours} Std</div>
        </div>

        <div class="flex flex-col mt-4 w-full">
            {#each data.timeSheet.entries as entry, index}
                {#if entry}
                    <div style="grid-template-columns: 6% auto;" class="grid gap-2 border-b px-2">
                        <div class="border-r pr-2 font-bold">{entry.day}</div>
                        <div class="flex justify-between w-full">
                            <div>{entry.start} - {entry.end} (Pause: {entry.breakDuration} Min)</div>
                            <div>
                                {entry.totalHours} Std
                                <button onclick={() => editEntry(entry.id)}>
                                    <Pen size="16" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {#if editingEntryId === entry.id}
                        <SlidingOverlay close={() => editingEntryId = null}>
                            {#snippet title()}
                                Eintrag für Tag {entry.day} bearbeiten
                            {/snippet}

                            {#snippet body()}
                                <div class="flex flex-col gap-2 border-b pb-4 mb-4">
                                    <label>
                                        <span class="label-text">Beginn</span>
                                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Beginn (8:00)" bind:value={start}/>
                                    </label>
                                    <label>
                                        <span class="label-text">Pause (Minuten)</span>
                                        <input class="input bg-surface-50" type="number" placeholder="Pause (60 Min)" bind:value={breakDuration}/>
                                    </label>
                                    <label>
                                        <span class="label-text">Ende</span>
                                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Ende (17:30)" bind:value={end}/>
                                    </label>
                                    <button onclick={() => updateForDay(index + 1, { id: entry.id, start, breakDuration, end })} type="button" class="btn preset-filled">
                                        Speichern
                                        <ChevronDown />
                                    </button>
                                </div>
                            {/snippet}
                        </SlidingOverlay>
                    {/if}
                {:else if index < new Date().getDate() - 1}
                    <div style="grid-template-columns: 6% auto;" class="grid gap-2 border-b w-full bg-surface-100-900 px-2">
                        <div class="border-r pr-2 font-bold">{index + 1}</div>

                        <button class="btn btn-sm preset-filled my-2" onclick={() => dayIndex = index + 1}>
                            Hinzufügen
                            <ChevronRight size="16"/>
                        </button>
                    </div>

                    {#if dayIndex !== null}
                        <SlidingOverlay close={() => dayIndex = null}>
                            {#snippet title()}
                                Neuer Eintrag für Tag {dayIndex}
                            {/snippet}

                            {#snippet body()}
                                <div class="flex flex-col gap-2 border-b pb-4 mb-4">
                                    <label>
                                        <span class="label-text">Beginn</span>
                                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Beginn (8:00)" bind:value={start}/>
                                    </label>
                                    <label>
                                        <span class="label-text">Pause (Minuten)</span>
                                        <input class="input bg-surface-50" type="number" placeholder="Pause (60 Min)" bind:value={breakDuration}/>
                                    </label>
                                    <label>
                                        <span class="label-text">Ende</span>
                                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Ende (17:00)" bind:value={end}/>
                                    </label>
                                    <button onclick={() => saveForDay(dayIndex)} type="button" class="btn preset-filled">
                                        Tag hinzufügen
                                        <ChevronDown />
                                    </button>
                                </div>
                            {/snippet}
                        </SlidingOverlay>
                    {/if}
                {/if}
            {/each}
        </div>
    {/if}
</main>