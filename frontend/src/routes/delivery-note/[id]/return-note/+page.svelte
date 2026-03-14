<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi";
import { ChevronRight } from "@lucide/svelte";
import { createToaster, Toast } from "@skeletonlabs/skeleton-svelte";

interface ReturnNoteProductDto {
    deliveryNoteProductId: string;
    returnedTotal: number|null;
    returnedBottles: number|null;
    returnedFull: number|null;
    returnedFullBottles: number|null;
}

interface CreateReturnNoteRequestDto {
    deliveryNoteId: string;
    returnNoteProducts: ReturnNoteProductDto[];
}

const { data } = $props();

let errorText: string = $state('');

let toaster = createToaster();

let returnNote: CreateReturnNoteRequestDto = {
    deliveryNoteId: data.deliveryNote.id,
    returnNoteProducts: data.deliveryNote.deliveryNoteProducts.map((product) => ({
        deliveryNoteProductId: product.id,
        returnedTotal: null,
        returnedTotalBottles: null,
        returnedFull: null,
        returnedFullBottles: null
    }))
};

const saveReturnNote = async () => {
    console.log(returnNote);

    if([...document.querySelectorAll('input[required]')].some((input: HTMLInputElement) => input.value === '')) {
        toaster.warning({
            title: 'Fehler',
            description: 'Bitte fülle alle Felder aus.'
        });

        return;
    }

    const json = await fetchApi(`delivery-notes/${returnNote.deliveryNoteId}/return-note`, 'POST', returnNote);

    console.log(json);
}
</script>

<PageHeadline>Rückschrift erstellen</PageHeadline>

<main class="lg:max-w-200 mx-auto">
    <div class="flex flex-col gap-2">
        {#each data.deliveryNote.deliveryNoteProducts as product, i}
            <div class="flex justify-between px-2">
                <div>{product.product.name}</div>
                <div>{product.quantity}</div>
            </div>
            {#if product.product.rentable}
                <div class="mb-2 px-2 border-b border-surface-200-800  pb-2">
                    <input
                        required
                        class="input mx-2"
                        type="number"
                        min="0"
                        max={product.quantity}
                        placeholder="Gesamt"
                        bind:value={returnNote.returnNoteProducts[i].returnedTotal}
                    />
                </div>
            {:else if product.product.quantityInCrate === null}
                <div class="mb-2 px-2 border-b border-surface-200-800 pb-2">
                    <input
                        required
                        class="input"
                        type="number"
                        min="0"
                        max={product.quantity}
                        placeholder="Gesamt"
                        bind:value={returnNote.returnNoteProducts[i].returnedTotal}
                    />
                </div>
            {:else if product.product.quantityInCrate > 0}
                <div class="px-2 flex justify-between gap-2">
                    <input
                        required
                        class="input"
                        type="number" 
                        placeholder="Vollgut"
                        min="0"
                        max={product.quantity}
                        bind:value={returnNote.returnNoteProducts[i].returnedFull}
                    />
                    <input
                        required
                        class="input"
                        type="number"
                        placeholder="Gesamt"
                        min="0"
                        max={product.quantity}
                        bind:value={returnNote.returnNoteProducts[i].returnedTotal}
                    />
                </div>
                <div class="mb-2 px-2 flex justify-between gap-2 border-b border-surface-200-800 pb-2">
                    <input
                        required
                        class="input"
                        type="number"
                        placeholder="Vollgut Flaschen"
                        min="0"
                        max={product.product.quantityInCrate}
                        bind:value={returnNote.returnNoteProducts[i].returnedFullBottles}
                    />
                    <input
                        required
                        class="input"
                        type="number"
                        placeholder="Gesamt Flaschen"
                        min="0"
                        max={product.product.quantityInCrate}
                        bind:value={returnNote.returnNoteProducts[i].returnedBottles}
                    />
                </div>
            {/if}
        {/each}
    </div>

    <div class="mt-4 mx-2">
        <Toast.Group {toaster}>
            {#snippet children(toast)}
            <Toast {toast}>
                <Toast.Message>
                    <Toast.Title>{toast.title}</Toast.Title>
                    <Toast.Description>{toast.description}</Toast.Description>
                </Toast.Message>
                <Toast.CloseTrigger />
            </Toast>
            {/snippet}
        </Toast.Group>

        <button onclick={saveReturnNote} type="button" class="btn preset-filled w-full mb-150">
            <span>Speichern</span>
            <ChevronRight />
        </button>
    </div>
</main>

<style>
input:user-invalid {
    border: 1px red solid;
}
</style>