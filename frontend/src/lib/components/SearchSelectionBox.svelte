<script lang="ts">
import type { Snippet } from "svelte";

type SelectableItem = { id: string, name: string };

let { 
    input = $bindable(),
    items = $bindable(),
    searchItem,
    onSelect,
    label,
    content,
    aboveContent = null,
    placeholder = "",
}: {
    input: string,
    items: Array<SelectableItem>,
    searchItem: (event: Event) => void,
    onSelect: (item: SelectableItem) => void,
    label: string,
    content: Snippet<[SelectableItem, (item: SelectableItem) => void]>,
    aboveContent?: Snippet<[]> | null,
    placeholder?: string,
} = $props();
</script>

<div class="relative">
    <label class="label">
        <span class="label-text">{label}</span>
        <input class="input bg-surface-50-950" type="text" oninput={searchItem} bind:value={input} placeholder={placeholder}/>
    </label>

    {#if input.length > 0}
        {@render aboveContent?.()}
    {/if}

    {#if items.length > 0 && input.length > 0}
        <div class="absolute flex flex-col bg-surface-50-950 border w-full max-h-[50vh] overflow-y-auto border-gray-300 rounded-md shadow-lg z-50">
            {#each items as item}
                {@render content(item, onSelect)}
            {/each}
        </div>
    {/if}
</div>