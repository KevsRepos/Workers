<script>
import PageHeadline from '$lib/components/PageHeadline.svelte';
import PrintedTimeSheet from '$lib/components/timeSheet/PrintedTimeSheet.svelte';
import TopNavigation from '$lib/components/TopNavigation.svelte';
import { Printer } from '@lucide/svelte';
import { Navigation } from '@skeletonlabs/skeleton-svelte';
import { tick } from 'svelte';

const { data } = $props();

let printing = $state(false);

const printTimeSheet = async () => {
    printing = true;

    await tick();
    window.print();
}

$inspect(data);
</script>

<svelte:window onafterprint={async () => {printing = false;}} />

{#if !printing}
    <TopNavigation>
        <Navigation.TriggerAnchor onclick={printTimeSheet}>
            <Printer />
            <Navigation.TriggerText>Drucken</Navigation.TriggerText>
        </Navigation.TriggerAnchor>
    </TopNavigation>

    <PageHeadline>Zeiterfassung {data.month.toString().padStart(2, '0')}/{data.year}</PageHeadline>

    <main>
        <table class="table">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>Start</th>
                    <th>Pause (Minuten)</th>
                    <th class="text-right">Ende</th>
                </tr>
            </thead>
            <tbody>
                {#each data.timeSheet.entries as entry, index}
                    {#if entry}
                        <tr>
                            <td>{entry.day}</td>
                            <td>{entry.start}h</td>
                            <td>{entry.breakDuration} Min</td>
                            <td class="text-right">{entry.end}h</td>
                        </tr>
                    {:else}
                        <tr class="opacity-30">
                            <td>{index + 1}</td>
                            <td colspan="3">Kein Eintrag vorhanden</td>
                        </tr>
                    {/if}
                {/each}
            </tbody>
        </table>
    </main>
{:else}
    <PrintedTimeSheet timeSheet={data.timeSheet} />
{/if}