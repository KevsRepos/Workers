<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import { Combobox, Portal, type ComboboxRootProps, useListCollection } from "@skeletonlabs/skeleton-svelte";
import { CircleX } from "@lucide/svelte";

let { name = $bindable(), selectedProducts = $bindable(), onSave, saving = false, error = '', success = '' } = $props<{
    name: string;
    selectedProducts: { id: string; name: string }[];
    onSave: () => void;
    saving?: boolean;
    error?: string;
    success?: string;
}>();

let searchResults = $state<any[]>([]);
let searchTimeout: ReturnType<typeof setTimeout>;

const collection = $derived(useListCollection({
    items: searchResults,
    itemToString: (item) => item.name,
    itemToValue: (item) => item
}));

const onSearch: ComboboxRootProps['onInputValueChange'] = async (event) => {
    const query = event.inputValue;

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            searchResults = [];
            return;
        }

        searchResults = await fetchApi(`products/search?productName=${encodeURIComponent(query)}`, 'GET');
    }, 300);
};

const onSelect: ComboboxRootProps['onSelect'] = (event) => {
    const product = event.itemValue;
    if (!selectedProducts.some(p => p.id === product.id)) {
        selectedProducts.push({ id: product.id, name: product.name });
    }
};

const removeProduct = (index: number) => {
    selectedProducts.splice(index, 1);
};
</script>

<main class="p-4 flex flex-col gap-4 md:max-w-200 mx-auto">
    <label class="label">
        <span class="label-text">Name</span>
        <input class="input" type="text" bind:value={name} />
    </label>

    <Combobox {collection} onSelect={onSelect} onInputValueChange={onSearch} placeholder="Artikel suchen" multiple>
        <Combobox.Label>Artikel</Combobox.Label>
        <Combobox.Control>
            <Combobox.Input />
        </Combobox.Control>
        <Portal>
            <Combobox.Positioner>
                {#if searchResults.length}
                    <Combobox.Content>
                        {#each searchResults as item}
                            <Combobox.Item {item}>
                                <Combobox.ItemText>{item.name}</Combobox.ItemText>
                                <Combobox.ItemIndicator />
                            </Combobox.Item>
                        {/each}
                    </Combobox.Content>
                {/if}
            </Combobox.Positioner>
        </Portal>
    </Combobox>

    <div class="flex flex-col gap-2">
        {#each selectedProducts as product, index}
            <div class="flex row gap-2 items-center bg-surface-200-800 p-2 rounded">
                <button onclick={() => removeProduct(index)} type="button">
                    <CircleX />
                </button>
                <div>{product.name}</div>
            </div>
        {/each}
    </div>

    {#if error}
        <p class="text-red-500">{error}</p>
    {/if}
    {#if success}
        <p class="text-green-500">{success}</p>
    {/if}

    <button class="btn preset-filled" onclick={onSave} disabled={saving || selectedProducts.length < 2}>
        {saving ? 'Speichert...' : 'Speichern'}
    </button>
</main>
