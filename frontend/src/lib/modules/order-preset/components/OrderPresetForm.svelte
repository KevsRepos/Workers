<script lang="ts">
import Input from "$lib/components/forms/Input.svelte";
import SearchSelectionBox from "$lib/components/forms/SearchSelectionBox.svelte";
import { searchProducts } from "$lib/modules/product/api";
import { CircleX } from "@lucide/svelte";
import { OrderPreset, type OrderPresetItem, type OrderPresetProduct } from "$lib/modules/order-preset/model/OrderPreset";

let {
    orderPreset,
    onSubmit
}: {
    orderPreset: OrderPreset;
    onSubmit: (orderPreset: OrderPreset) => Promise<void>;
} = $props();

let name = $state("");

let productSearch: HTMLInputElement | null = $state(null);
let productSearchValue = $state("");

let items = $state<OrderPresetProduct[]>([]);
let selectedItems = $state<OrderPresetProduct[]>([]);

$effect(() => {
    name = orderPreset.name;
    selectedItems = orderPreset.items.map((item: OrderPresetItem) => item.product);
});

let searchTimeout: ReturnType<typeof setTimeout>;

const onSearch = async (event: Event) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(async () => {
        const query = (event.target as HTMLInputElement).value;

        if (query.length < 2) {
            items = [];
            return;
        }
        
        items = await searchProducts(query) as OrderPresetProduct[];
    }, 300);
};

const selectItem = (item: OrderPresetProduct) => {
    if (!selectedItems.some((selectedItem) => selectedItem.id === item.id)) {
        selectedItems.push(item);
    }
    items = [];
    productSearchValue = "";
};

const removeItem = (index: number) => {
    selectedItems = selectedItems.filter((_, i) => i !== index);
};

const handleSubmit = async () => {
    const submittedOrderPreset = new OrderPreset(
        orderPreset.id,
        name,
        selectedItems.map((product) => ({ id: null, product }))
    );
    await onSubmit(submittedOrderPreset);
};
</script>

<div class="flex flex-col gap-4">
    <Input
        label="Name"
        placeholder="Name der Bestellvorlage"
        bind:value={name}
    />

    <SearchSelectionBox 
        inputElement={productSearch}
        bind:input={productSearchValue}
        searchItem={onSearch}
        items={items}
        onSelect={selectItem}
        label="Artikel suchen"
    >
        {#snippet content(item, onSelect)}
            <button type="button" onclick={() => onSelect(item)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{item.name}</button>
        {/snippet}
    </SearchSelectionBox>

    <div class="flex flex-col gap-2 mt-2 divider-y overflow-y-auto">
        {#each selectedItems as product, index (product.id)}
            <div class="flex row gap-2 justify-between items-center bg-surface-200-800 p-2 rounded">
                <div class="flex gap-2 items-center">
                    <button onclick={() => removeItem(index)} type="button" aria-label="Artikel entfernen" title="Artikel entfernen">
                        <CircleX />
                    </button>
                    <div>{product.name}</div>
                </div>
            </div>
        {/each}
    </div>

    <button type="button" onclick={handleSubmit} disabled={selectedItems.length === 0 || !name} class="btn preset-filled">Speichern</button>
</div>