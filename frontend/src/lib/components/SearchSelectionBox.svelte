<script lang="ts">
import type { Snippet } from "svelte";

type SelectableItem = { id: string, name: string };

let { 
    inputElement = $bindable(),
    input = $bindable(),
    items = $bindable(),
    searchItem,
    onSelect,
    label,
    content,
    aboveContent = null,
    placeholder = "",
}: {
    inputElement: HTMLInputElement | null | undefined,
    input: string,
    items: Array<SelectableItem>,
    searchItem: (event: Event) => void,
    onSelect: (item: SelectableItem) => void,
    label: string,
    content: Snippet<[SelectableItem, (item: SelectableItem) => void]>,
    aboveContent?: Snippet<[]> | null,
    placeholder?: string,
} = $props();

let preSelectedIndex: number | null = $state(null);

const moveInList = (event: KeyboardEvent) => {
    if (!items.length) return;

    if (event.key === "ArrowDown") {
        event.preventDefault();
        preSelectedIndex = (preSelectedIndex === null || preSelectedIndex === items.length) ? 0 : preSelectedIndex + 1;
    }

    if (event.key === "ArrowUp") {
        event.preventDefault();
        preSelectedIndex = (preSelectedIndex === null || preSelectedIndex === 0) ? items.length - 1 : preSelectedIndex - 1;
    }

    if (event.key === "Enter" && preSelectedIndex !== null) {
        onSelect(items[preSelectedIndex]);
        preSelectedIndex = null;
    }
};
</script>

<svelte:window onkeydown={moveInList} />

<div class="relative">
    <label class="label">
        <span class="label-text">{label}</span>
        <input class="input bg-surface-50-950" type="text" oninput={searchItem} bind:value={input} bind:this={inputElement} placeholder={placeholder}/>
    </label>

    {#if input.length > 0}
        {@render aboveContent?.()}
    {/if}

    {#if items.length > 0 && input.length > 0}
        <div class="absolute flex flex-col bg-surface-50-950 border w-full max-h-[50vh] overflow-y-auto border-gray-300 rounded-md shadow-lg z-50">
            {#each items as item}
                <div class={preSelectedIndex === items.indexOf(item) ? "bg-gray-200" : ""}>
                    {@render content(item, onSelect)}
                </div>
            {/each}
        </div>
    {/if}
</div>