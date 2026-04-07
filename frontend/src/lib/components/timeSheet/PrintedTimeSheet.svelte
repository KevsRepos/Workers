<script lang="ts">
let { timeSheet } = $props();
</script>

<div class="page p-4">
    <h2 class="font-bold mb-2">Zeiterfassung {timeSheet.month.toString().padStart(2, '0')}/{timeSheet.year}</h2>

    <div>{timeSheet.accountFirstName} {timeSheet.accountSurname}</div>

    <table class="w-full">
        <thead>
            <tr>
                <th class="text-left">Tag</th>
                <th class="text-left">Start</th>
                <th class="text-center">Pause (Minuten)</th>
                <th class="text-right">Ende</th>
                <th class="text-right">Gesamt</th>
            </tr>
        </thead>
        <tbody>
            {#each timeSheet.entries as entry, index}
                {#if entry}
                <tr>
                    <td class="text-left">{entry.day}</td>
                    <td class="text-left">{entry.start}h</td>
                    <td class="text-center">{entry.breakDuration} Min</td>
                    <td class="text-right">{entry.end}h</td>
                    <td class="text-right">{entry.totalHours}h</td>
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

    <div class="text-right mt-4">Gesamtstunden: {timeSheet.totalHours} Std</div>
</div>

<style>
@media print {
    @page{
        size: A4;
        margin: 20mm;
    }
    .page {
        min-height: 100vh;
        break-after: page;
    }
    :global(header) {
        display: none;
    }
    :global([data-scope="navigation"]) {
        display: none;
    }
    tr {
        border-bottom: 0.06cm rgb(48, 48, 48) solid;
    }
}
</style>