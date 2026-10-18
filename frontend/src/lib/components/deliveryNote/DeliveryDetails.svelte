<script lang="ts">
import DatePicker from "../DatePicker.svelte";

let { deliveryNoteForm, setDeliveryNoteDate, parseDate } = $props();
</script>

<div class="grid grid-cols-2 gap-4">
    <label class="flex items-center justify-center h-20 bg-surface-50-950 rounded-2xl gap-2">
        <input bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={true}/>
        <p>Zum ausliefern</p>
    </label>

    <label class="flex h-20 bg-surface-50-950 rounded-2xl items-center justify-center gap-2">
        <input bind:group={deliveryNoteForm.delivery} class="radio" type="radio" name="delivery" value={false}/>
        <p>Selbstabholer</p>
    </label>
</div>

<DatePicker class="mt-4" label="Lieferdatum" bind:value={
    () => deliveryNoteForm.deliveryDate.slice(0, 10),
    (v) => setDeliveryNoteDate(v)
}></DatePicker>

{#if deliveryNoteForm.deliveryDate}
    {#key deliveryNoteForm.deliveryDate}
        <DatePicker class="mt-4" label="Abholdatum" bind:value={
            () => deliveryNoteForm.pickupDate && deliveryNoteForm.pickupDate.slice(0, 10),
            (v) => deliveryNoteForm.pickupDate = v
        } min={parseDate(deliveryNoteForm.deliveryDate.slice(0, 10))}></DatePicker>
    {/key}
{/if}