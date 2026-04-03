<script>
import PageHeadline from "$lib/components/PageHeadline.svelte";

</script>

<PageHeadline>Startseite</PageHeadline>

<main class="lg:max-w-200 mx-auto flex flex-col gap-4">
    <a class="block p-2 border-b" href="/delivery-note/list/1">
        <div class="font-bold text-2xl">Lieferscheine</div>
        <p>
             Verwalten von Lieferscheinen, Erstellen neuer Lieferscheine, Bearbeiten bestehender Lieferscheine, Erstellen von Rückschriften und Drucken von Lieferscheinen und Rückschriften.
        </p>
    </a>
    <a class="block p-2 border-b" href="/time-tracking">
        <div class="font-bold text-2xl">Zeiterfassung</div>
        <p>
             Verwalten von Zeiterfassungen, Erstellen neuer Zeiterfassungen, Bearbeiten bestehender Zeiterfassungen, Erstellen von Rückschriften und Drucken von Zeiterfassungen und Rückschriften.
        </p>
    </a>
</main>