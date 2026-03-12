<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import { ChevronRight } from "@lucide/svelte";

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

$inspect(data);

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

    const json = await fetchApi(`delivery-notes/${returnNote.deliveryNoteId}/return-note`, 'POST', returnNote);

    console.log(json);
}
</script>


<main>
    <h1 class="mt-2 mb-2 pb-2 px-4 text-center border-b border-surface-200-800">Zurückschreiben</h1>

    <table class="">
        <tbody>
            {#each data.deliveryNote.deliveryNoteProducts as product, i}
                <tr>
                    <td class="pt-2 px-2">{product.product.name}</td>
                    <td class="pt-2 px-2 text-right">{product.quantity}</td>
                </tr>
                {#if product.product.rentable}
                    <tr>
                        <td colspan="2" class="pb-2 px-2">
                            <input class="input" type="number" min="1" max={product.quantity} placeholder="Gesamt"
                                bind:value={returnNote.returnNoteProducts[i].returnedTotal} />
                        </td>
                    </tr>
                {:else if product.product.quantityInCrate === null}
                    <tr>
                        <td colspan="2" class="pb-2 px-2">
                            <input class="input" type="number" min="1" max={product.quantity} placeholder="Gesamt"
                                bind:value={returnNote.returnNoteProducts[i].returnedTotal} />
                        </td>
                    </tr>
                {:else if product.product.quantityInCrate > 0}
                    <tr class="mb-2">
                        <td class="pb-2 px-2">
                            <input class="input" type="number" placeholder="Vollgut" min="0"
                                bind:value={returnNote.returnNoteProducts[i].returnedFull} />
                        </td>
                        <td class="pb-2 px-2">
                            <input class="input" type="number" placeholder="Gesamt" min="0"
                                bind:value={returnNote.returnNoteProducts[i].returnedTotal} />
                        </td>
                    </tr>
                    <tr class="border-b border-surface-200-800 mb-2">
                        <td class="pb-2 px-2">
                            <input class="input" type="number" placeholder="Vollgut Flaschen" min="0"
                                bind:value={returnNote.returnNoteProducts[i].returnedFullBottles} />
                        </td>
                        <td class="pb-2 px-2">
                            <input class="input" type="number" placeholder="Gesamt Flaschen" min="0"
                                bind:value={returnNote.returnNoteProducts[i].returnedBottles} />
                        </td>
                    </tr>
                {/if}
            {/each}
        </tbody>
    </table>

    <div class="mt-4 mx-2">
        <button onclick={saveReturnNote} type="button" class="btn preset-filled w-full mb-150">
            <span>Speichern</span>
            <ChevronRight />
        </button>
    </div>
</main>