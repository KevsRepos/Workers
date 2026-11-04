<script lang="ts">
import Dialog from "../Dialog.svelte";
import { fetchApi } from "$lib/fetchApi";
import { addProductToDeliveryNote } from "$lib/deliveryNote/actions";
import type { Preset } from "$lib/interfaces/Preset";

let { open = $bindable(), deliveryNote } = $props();

const fetchPresets = async () => {
    const json = await fetchApi(`order-presets`, 'GET');

    return json;
}

const addPreset = (preset: Preset) => {
    const products = preset.items.map(item => item.product);

    for (const product of products) {
        addProductToDeliveryNote(deliveryNote, product.id, null, product.name);
    }

    open = false;
}
</script>

{#if open}
    <Dialog close={() => open = false}>
        {#snippet title()}
            Vorlage auswählen
        {/snippet}
        {#snippet body()}
            {#await fetchPresets()}
                Lädt...
            {:then data}
                <div class="md:grid grid-cols-2 flex flex-col">
                    {#each data as preset, i}
                        <button onclick={() => addPreset(preset)} class="text-left p-2 {i % 2 === 0 ? 'md:border-r' : ''} max-md:border-b {i < 2 ? 'md:border-b' : ''} border-surface-300-700">
                            <div class="font-bold">{preset.name}</div>
                            <div>
                                {preset.items.slice(0, 3).map((p: any) => `${p.product.name}`).join(', ')}
                            </div>
                        </button>
                    {/each}
                </div>
            {/await}
        {/snippet}
    </Dialog>
{/if}