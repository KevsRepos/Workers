<script lang="ts">
import { goto } from "$app/navigation";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi";
import { ChevronRight } from "@lucide/svelte";
import { createToaster, Toast } from "@skeletonlabs/skeleton-svelte";
import type { ReturnUnion } from "$lib/interfaces/DeliveryNote";

const { data } = $props();

let deliveryNote = $state(data.deliveryNote);
let returnUnions: ReturnUnion[] = $state(data.returnUnions);

let unionReturnData = $state(
    returnUnions.map(() => ({
        returnedTotal: null as number | null,
        returnedTotalBottles: null as number | null,
        returnedFull: null as number | null,
        returnedFullBottles: null as number | null,
    }))
);

let toaster = createToaster();

const saveReturnNote = async () => {
    if([...document.querySelectorAll('input[required]')].some((input: HTMLInputElement) => input.value === '')) {
        toaster.warning({
            title: 'Fehler',
            description: 'Bitte fülle alle Felder aus.'
        });

        return;
    }

    const payload = {
        deliveryNoteId: deliveryNote.id,
        returnNoteEntries: returnUnions.map((union, i) => ({
            deliveryNoteProductIds: union.deliveryNoteProductIds,
            returnedTotal: unionReturnData[i].returnedTotal,
            returnedTotalBottles: unionReturnData[i].returnedTotalBottles,
            returnedFull: unionReturnData[i].returnedFull,
            returnedFullBottles: unionReturnData[i].returnedFullBottles,
        }))
    };
    
    try {
        await fetchApi(`delivery-notes/${deliveryNote.id}/return-note`, 'POST', payload);

        goto(`/delivery-note/${deliveryNote.id}`);
    } catch(error) {
        toaster.error({
            title: 'Fehler',
            description: error.message
        });
    }
}
</script>

<PageHeadline>Rückschrift erstellen</PageHeadline>

<main class="lg:max-w-200 mx-auto">
    <div class="flex flex-col gap-2">
        {#each returnUnions as union, i}

            <div class="flex justify-between px-2">
                <div>
                    {union.name}
                    {#if union.isUnion}
                        <span class="text-surface-500 text-sm">(Zusammengefasste Einheit)</span>
                    {/if}
                </div>
                <div>{union.quantity}</div>
            </div>

            {#if union.rentable}
                <div class="mb-2 px-2 border-b border-surface-200-800 pb-2">
                    <input
                        required
                        class="input"
                        type="number"
                        min="0"
                        max={union.quantity}
                        placeholder="Zurück {i + 1}"
                        bind:value={unionReturnData[i].returnedFull}
                    />
                </div>
            {:else if union.quantityInCrate === null}
                <div class="mb-2 px-2 border-b border-surface-200-800 pb-2">
                    <input
                        required
                        class="input"
                        type="number"
                        min="0"
                        max={union.quantity}
                        placeholder="Zurück {i + 1}"
                        bind:value={unionReturnData[i].returnedFull}
                    />
                </div>
            {:else if union.quantityInCrate > 0}
                <div class="grid grid-template-areas mb-2 px-2 border-b border-surface-200-800 pb-2 gap-2">
                    <input
                        required
                        class="input grid-area-first"
                        type="number" 
                        placeholder="Vollgut"
                        min="0"
                        max={union.quantity}
                        bind:value={unionReturnData[i].returnedFull}
                    />
                    <input
                        required
                        class="input grid-area-third"
                        type="number"
                        placeholder="Vollgut Flaschen"
                        min="0"
                        max={union.quantityInCrate}
                        bind:value={unionReturnData[i].returnedFullBottles}
                    />
                    <input
                        required
                        class="input grid-area-second"
                        type="number"
                        placeholder="Gesamt"
                        min="0"
                        max={union.quantity}
                        bind:value={unionReturnData[i].returnedTotal}
                    />
                    <input
                        required
                        class="input grid-area-fourth"
                        type="number"
                        placeholder="Gesamt Flaschen"
                        min="0"
                        max={union.quantityInCrate}
                        bind:value={unionReturnData[i].returnedTotalBottles}
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
.grid-template-areas {
    grid-template-areas: "first second" "third fourth";
}
.grid-area-first {
    grid-area: first;
}
.grid-area-second {
    grid-area: second;
}
.grid-area-third {
    grid-area: third;
}
.grid-area-fourth {
    grid-area: fourth;
}
</style>