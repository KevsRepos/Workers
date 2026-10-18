<script lang="ts">
let { deliveryNoteForm, step = $bindable(), saveDeliveryNote, deliveryNoteIsValid, formatDate } = $props<{
    deliveryNoteForm: any;
    step: number;
    saveDeliveryNote: () => void;
    deliveryNoteIsValid: boolean;
    formatDate: (date: string) => string;
}>();
</script>

<div class="border-b py-2">
    <strong>Kunde:</strong>
    {#if deliveryNoteForm.customer}
        {deliveryNoteForm.customer.firstName} {deliveryNoteForm.customer.surname}<br />
    {:else}
        <button onclick={() => step = 0} class="btn preset-outlined ml-2">Setzen</button>
    {/if}
</div>

<div class="border-b py-2">
    <strong>Kurzbeschreibung:</strong>
    {#if deliveryNoteForm.shortDescription}
        {deliveryNoteForm.shortDescription}<br />
    {:else}
        <button onclick={() => step = 1} class="btn preset-outlined ml-2">Setzen</button>
    {/if}
</div>

<div class="border-b py-2">
    <strong>Lieferung:</strong>
    {#if deliveryNoteForm.delivery !== null}
        {deliveryNoteForm.delivery ? 'Zum ausliefern' : 'Selbstabholer'}<br />
    {:else}
        <button onclick={() => step = 2} class="btn preset-outlined ml-2">Setzen</button>
    {/if}
</div>

    <div class="border-b py-2">
    <strong>Lieferdatum:</strong>
    {#if deliveryNoteForm.deliveryDate}
        {formatDate(deliveryNoteForm.deliveryDate)}<br />
    {:else}
        <button onclick={() => step = 2} class="btn preset-outlined ml-2">Setzen</button>
    {/if}
</div>

<div class="border-b py-2">
    <strong>Artikel:</strong>
    {#if deliveryNoteForm.products.length > 0}
        <ul>
            {#each deliveryNoteForm.products as product}
                {#if product.quantity}
                    <li>{product.name} - {product.quantity} Stk.</li>
                {:else}
                    <li>{product.name} - <span class="badge preset-tonal-surface">Keine Menge</span></li>
                {/if}
            {/each}
        </ul>
    {:else}
        <button onclick={() => step = 3} class="btn preset-outlined ml-2">Setzen</button>
    {/if}
</div>

<button class="btn preset-filled-surface-950-50 w-full mt-4" disabled={!deliveryNoteIsValid} onclick={() => {saveDeliveryNote()}}>Speichern</button>