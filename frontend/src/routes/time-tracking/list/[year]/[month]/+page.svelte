<script lang="ts">
import { invalidateAll } from '$app/navigation';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import Drawer from '$lib/components/Drawer.svelte';
import PrintedTimeSheet from '$lib/components/timeSheet/PrintedTimeSheet.svelte';
import TopNavigation from '$lib/components/TopNavigation.svelte';
import { fetchApi } from '$lib/fetchApi';
import { ChevronDown, ChevronRight, Hospital, Pen, PartyPopper, Printer, TreePalm } from '@lucide/svelte';

const { data } = $props();

let start = $state('');
let breakDuration = $state(0);
let end = $state('');

let editingEntryId: string | null = $state(null);
let addingDay: number | null = $state(null);

const saveEntry = async (day: number, entryStart: string, entryBreakDuration: number, entryEnd: string) => {
    await fetchApi(`time-sheets/${data.timeSheet.id}/entries`, 'POST', {
        day,
        start: entryStart,
        breakDuration: entryBreakDuration,
        end: entryEnd
    });
    await invalidateAll();
    addingDay = null;
    start = '';
    breakDuration = 0;
    end = '';
};

const saveEntryType = async (day: number, type: string) => {
    await fetchApi(`time-sheets/${data.timeSheet.id}/entries`, 'POST', { day, type });
    await invalidateAll();
    addingDay = null;
};

const updateEntry = async (entryId: string, day: number, entryStart: string, entryBreakDuration: number, entryEnd: string) => {
    await fetchApi(`time-sheets/entries/${entryId}`, 'PUT', {
        day,
        start: entryStart,
        breakDuration: entryBreakDuration,
        end: entryEnd
    });
    await invalidateAll();
    editingEntryId = null;
    start = '';
    breakDuration = 0;
    end = '';
};

const updateEntryType = async (entryId: string, type: string) => {
    await fetchApi(`time-sheets/entries/${entryId}`, 'PUT', { type });
    await invalidateAll();
    editingEntryId = null;
};

const editEntry = (entryId: string) => {
    const entry = data.timeSheet.entries.find((e: any) => e?.id === entryId);
    if (entry) {
        start = entry.start ?? '';
        breakDuration = entry.breakDuration ?? 0;
        end = entry.end ?? '';
    }
    editingEntryId = entryId;
};
</script>

<div class="screen-only">
    <!-- <TopNavigation>
        <Navigation.TriggerAnchor onclick={() => window.print()}>
            <Printer />
            <Navigation.TriggerText>Drucken</Navigation.TriggerText>
        </Navigation.TriggerAnchor>
    </TopNavigation> -->

    <PageHeadline>Zeiterfassung {data.month.toString().padStart(2, '0')}/{data.year}</PageHeadline>

    <main class="lg:max-w-200 mx-auto">
        {#if !data.timeSheet}
            <p class="text-center mt-8">Kein Stundeneintrag für diesen Monat vorhanden.</p>
        {:else}
        <div class="flex flex-col mt-4 w-full">
            {#each data.timeSheet.entries as entry, index}
                {#if entry}
                    <div style="grid-template-columns: 6% auto;" class="grid gap-2 border-b px-2">
                        <div class="border-r pr-2 font-bold">{entry.day}</div>
                        <div class="flex justify-between w-full">
                            <div>
                                {#if entry.type === 'work'}
                                    {entry.start} - {entry.end} (Pause: {entry.breakDuration} Min)
                                {:else if entry.type === 'vacation'}
                                    Urlaub
                                {:else if entry.type === 'sick_leave'}
                                    Krank
                                {:else if entry.type === 'holiday'}
                                    Feiertag
                                {/if}
                            </div>
                            <div>
                                {entry.totalHours} Std
                                <button onclick={() => editEntry(entry.id)}>
                                    <Pen size="16" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {#if editingEntryId === entry.id}
                        <Drawer close={() => editingEntryId = null}>
                            {#snippet title()}
                                Eintrag für Tag {entry.day} bearbeiten
                            {/snippet}
                            {#snippet body()}
                                <div class="flex flex-col gap-2 pb-4 mb-4">
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
                                        <input class="input bg-surface-50" step="1800" type="time" placeholder="Ende (17:30)" bind:value={end} />
                                    </label>
                                    <button onclick={() => updateEntry(entry.id, entry.day, start, breakDuration, end)} type="button" class="btn preset-filled">
                                        Speichern
                                        <ChevronRight />
                                    </button>

                                    <hr class="hr" />

                                    <button onclick={() => updateEntryType(entry.id, '4')} type="button" class="btn preset-filled">
                                        Urlaub <TreePalm />
                                    </button>
                                    <button onclick={() => updateEntryType(entry.id, '3')} type="button" class="btn preset-filled">
                                        Krank <Hospital />
                                    </button>
                                    <button onclick={() => updateEntryType(entry.id, '2')} type="button" class="btn preset-filled">
                                        Feiertag <PartyPopper />
                                    </button>
                                </div>
                            {/snippet}
                        </Drawer>
                    {/if}
                {:else}
                    <div style="grid-template-columns: 6% auto;" class="grid gap-2 border-b w-full bg-surface-100-900 px-2">
                        <div class="border-r pr-2 font-bold">{index + 1}</div>
                        <button class="btn btn-sm preset-filled my-2" onclick={() => addingDay = index + 1}>
                            Hinzufügen
                            <ChevronRight size="16" />
                        </button>
                    </div>

                    {#if addingDay === index + 1}
                        <Drawer close={() => addingDay = null}>
                            {#snippet title()}
                                Neuer Eintrag für Tag {addingDay}
                            {/snippet}
                            {#snippet body()}
                                <div class="flex flex-col gap-2 pb-4 mb-4">
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
                                    <button onclick={() => saveEntry(index + 1, start, breakDuration, end)} type="button" class="btn preset-filled">
                                        Tag hinzufügen
                                        <ChevronDown />
                                    </button>

                                    <hr class="hr" />

                                    <button onclick={() => saveEntryType(index + 1, '4')} type="button" class="btn preset-filled">
                                        Urlaub <TreePalm />
                                    </button>
                                    <button onclick={() => saveEntryType(index + 1, '3')} type="button" class="btn preset-filled">
                                        Krank <Hospital />
                                    </button>
                                    <button onclick={() => saveEntryType(index + 1, '2')} type="button" class="btn preset-filled">
                                        Feiertag <PartyPopper />
                                    </button>
                                </div>
                            {/snippet}
                        </Drawer>
                    {/if}
                {/if}
            {/each}
        </div>
        {/if}
    </main>
</div>

<div class="print-only">
    {#if data.timeSheet}
        <PrintedTimeSheet timeSheet={data.timeSheet} />
    {/if}
</div>

<style>
    .print-only { display: none; }

    @media print {
        .screen-only { display: none; }
        .print-only  { display: block; }
    }
</style>