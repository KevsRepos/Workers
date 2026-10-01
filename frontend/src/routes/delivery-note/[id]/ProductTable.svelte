<script lang="ts">
import { invalidate } from "$app/navigation";
import { fetchApi } from "$lib/fetchApi";
import { Pen } from "@lucide/svelte";
import { tick } from "svelte";

let { deliveryNote, returnUnions } = $props();

let productQuantities = $state(deliveryNote.deliveryNoteProducts.map((dnp) => {
    return {
        id: dnp.id,
        quantity: dnp.quantity ?? 0,
        productId: dnp.product.id
    }
}));

let quantityInputs = $state<Array<HTMLInputElement>>([]);

let quantityEditMode = $state(false);

const toggleQuantityEditMode = async () => {
    quantityEditMode = !quantityEditMode;

    if(quantityEditMode) {
        await tick();

        quantityInputs[0]?.focus();
    }
};

const jumpToNext = (event: KeyboardEvent) => {
    if(event.key !== 'Enter') return;

    const input = event.target as HTMLInputElement;

    if(parseInt(input.dataset.index!) === quantityInputs.length - 1) {
        saveQuantities();

        return;
    }

    quantityInputs[parseInt((event.target as HTMLInputElement).dataset.index!) + 1]?.focus();
}

const saveQuantities = async () => {
    await fetchApi(`delivery-notes/${deliveryNote.id}/products/quantities`, 'PUT', {
        products: productQuantities
    });

    quantityEditMode = false;

    invalidate((url) => url.pathname === `/delivery-notes/${deliveryNote.id}/return-unions`);
};

const keypressHandler = (evt: KeyboardEvent) => {
    if(evt.key === 'Enter' && !quantityEditMode) {
        toggleQuantityEditMode();
    }

    if(evt.key === 'Escape' && quantityEditMode) {
        quantityEditMode = false;
    }
};
</script>

<svelte:window onkeydown={keypressHandler} />

<table class="mt-4 table text-lg">
    <thead>
        <tr>
            <th>Artikel</th>
            <th class={{'flex items-center gap-2': true, 'justify-end': deliveryNote.status < 4, 'justify-center': deliveryNote.status >= 4}}>
                Menge
                <button onclick={toggleQuantityEditMode} class="cursor-pointer">
                    <Pen size="16" />
                </button>
            </th>
            {#if deliveryNote.status >= 4}
                <th class="text-center!">Zurück</th>
                <th class="text-center!">Gesamt</th>
            {/if}
        </tr>
    </thead>
    <tbody class="[&>tr>td]:border-l [&>tr>td]:border-r [&>tr>td]:border-surface-200-800 border-b border-surface-200-800">
        {#if deliveryNote.status >= 4}
            {#each returnUnions as union}
                <tr>
                    <td>
                        {union.name}
                        {#if union.isUnion}
                            <span class="text-surface-500 text-sm">(Zusammengefasste Einheit)</span>
                        {/if}
                    </td>
                    <td class="text-center!">{union.quantity} Stk.</td>
                    <td class="text-center!">
                        {#if union.returnNoteEntry?.returnedFull}
                            {union.returnNoteEntry.returnedFull} Stk.
                        {/if}
                        {#if union.returnNoteEntry?.returnedFullBottles}
                            <br />{union.returnNoteEntry.returnedFullBottles} Fl.
                        {/if}
                    </td>
                    <td class="text-right!">
                        {#if union.returnNoteEntry?.returnedTotal}
                            {#if union.deposit}
                                {union.returnNoteEntry.returnedTotal} * {(((union.deposit.crateAmount || 0) + (union.deposit.singleAmount * (union.quantityInCrate || 0))) / 100).toFixed(2)}€
                            {:else}
                                {union.returnNoteEntry.returnedTotal} Stk.
                            {/if}
                        {/if}
                        {#if union.returnNoteEntry?.returnedTotalBottles}
                            {#if union.deposit}
                                <br />{union.returnNoteEntry.returnedTotalBottles} * {(union.deposit.singleAmount / 100).toFixed(2)}€
                            {:else}
                                <br />{union.returnNoteEntry.returnedTotalBottles} Fl.
                            {/if}
                        {/if}
                    </td>
                </tr>
            {/each}
        {:else}
            {#each deliveryNote.deliveryNoteProducts as item, index}
                <tr>
                    <td>{item.product.name}</td>
                    <td class="text-right!">
                        {#if !quantityEditMode}
                            {#if item.quantity}
                                {item.quantity} Stk.
                            {:else}
                                <span class="badge preset-tonal-warning">Keine Menge</span>
                            {/if}
                        {:else}
                            <input bind:this={quantityInputs[index]} bind:value={productQuantities[index].quantity} min="1" data-index={index} type="number" class="input" placeholder="Menge" onkeydown={jumpToNext} />
                        {/if}
                    </td>
                </tr>
            {/each}
        {/if}
    </tbody>
</table>

{#if quantityEditMode}
    <button class="mt-2 btn preset-filled-surface-950-50 float-right" onclick={() => saveQuantities()}>Mengen speichern</button>
{/if}