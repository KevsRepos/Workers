<script lang="ts">
import Input from "../elements/Input.svelte";
import type { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

let { deliveryNoteForm }: { deliveryNoteForm: DeliveryNoteForm } = $props();
</script>

<div class="flex flex-col gap-4">
    <h2 class="font-bold border-b">Freitextinformationen</h2>
    <label>
        <span class="label-text">Informationen für den Kunden (Sichtbar auf Lieferschein)</span>
        <textarea
            class="textarea bg-surface-50-950"
            rows="5"
            placeholder="Optionale Informationen für den Kunden"
            bind:value={deliveryNoteForm.shortDescription}
        ></textarea>
    </label>

    <label>
        <span class="label-text">Private Informationen</span>
        <textarea
            class="textarea bg-surface-50-950"
            rows="5"
            placeholder="Optionale private Informationen"
            bind:value={deliveryNoteForm.privateDescription}
        ></textarea>
    </label>

    <h2 class="font-bold border-b">Gäste</h2>

    <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
        <Input label="Anzahl der Erwachsenen Gäste" type="number" min="0" bind:value={deliveryNoteForm.adultGuests} />
        <Input label="Anzahl der Kinder" type="number" min="0" bind:value={deliveryNoteForm.childGuests} />
    </fieldset>
</div>