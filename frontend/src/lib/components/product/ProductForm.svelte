<script lang="ts">
import { centToEuro } from "$lib/functions/helpers";
import type { Deposit } from "$lib/interfaces/Deposit.d.ts";

let { name = $bindable(), salesPrice = $bindable(), sellable = $bindable(), rentable = $bindable(), hasCrate = $bindable(), quantityInCrate = $bindable(), depositId = $bindable(), deposits, onSave, saving = false, error = '', success = '' } = $props<{
    name: string;
    salesPrice: number | string;
    sellable: boolean;
    rentable: boolean;
    hasCrate: boolean;
    quantityInCrate: number;
    depositId: string;
    deposits: Deposit[];
    onSave: () => void;
    saving?: boolean;
    error?: string;
    success?: string;
}>();

const setType = (type: 'sellable' | 'rentable') => {
    if (type === 'sellable') {
        sellable = true;
        rentable = false;
    } else {
        sellable = false;
        rentable = true;
    }
}
</script>

<main class="p-4 flex flex-col gap-4 md:max-w-200 mx-auto">
    <label class="label">
        <span class="label-text">Name</span>
        <input class="input bg-surface-100-900" type="text" bind:value={name} />
    </label>

    <label class="label">
        <span class="label-text">Verkaufspreis (Cent)</span>
        <input class="input bg-surface-100-900" type="number" bind:value={salesPrice} />
    </label>

    <div class="flex flex-col gap-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox" bind:checked={hasCrate} />
            <span class="label-text">In Kiste</span>
        </label>
        <input class="input bg-surface-100-900" type="number" min="1" bind:value={quantityInCrate} disabled={!hasCrate} />
    </div>

    <div class="flex gap-4">
        <label class="flex items-center gap-2">
            <input type="radio" class="radio" name="productType" checked={sellable} onchange={() => setType('sellable')} />
            <span class="label-text">Verkäuflich</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="radio" class="radio" name="productType" checked={rentable} onchange={() => setType('rentable')} />
            <span class="label-text">Leihbar</span>
        </label>
    </div>

    <label class="label">
        <span class="label-text">Pfand</span>
        <select class="select bg-surface-100-900 p-2" bind:value={depositId}>
            <option value="">Kein Pfand</option>
            {#each deposits as deposit}
                <option value={deposit.id}>
                    {centToEuro(deposit.singleAmount)} €
                    {#if deposit.crateAmount}
                        / {centToEuro(deposit.crateAmount)} € Kiste
                    {/if}
                </option>
            {/each}
        </select>
    </label>

    {#if error}
        <p class="text-red-500">{error}</p>
    {/if}
    {#if success}
        <p class="text-green-500">{success}</p>
    {/if}

    <button class="btn preset-filled" onclick={onSave} disabled={saving}>
        {saving ? 'Speichert...' : 'Speichern'}
    </button>
</main>