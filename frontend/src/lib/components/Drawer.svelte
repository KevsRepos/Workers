<script lang="ts">
    import { swipeGesture } from "$lib/attachments/swipeGesture";
    import { Minus, X } from "@lucide/svelte";
    import { slide } from "svelte/transition";

    let { close, title, body } = $props();

    let header: HTMLDivElement | null = $state(null);

    let open = $state(true);
</script>

<div
    {@attach swipeGesture({
        axis: "y",
        edge: "bottom",
        open,
        handle: () => header,
        canOpen: false,
        canClose: true,
        onClose: () => {
            open = false;
            close();
        }
    })}
    transition:slide
    class="fixed inset-x-0 bottom-0 z-50 bg-surface-100 text-surface-950 flex flex-col w-full h-4/6 rounded-2xl"
>
    <div bind:this={header} class="flex flex-col items-center">
        <Minus />
        <div class="flex justify-between px-4 pb-4 border-b w-full">
            <div class="font-bold flex items-center">
                {@render title()}
            </div>

            <button onclick={close}>
                <X />
            </button>
        </div>
    </div>

    <div class="p-2">
        {@render body()}
    </div>
</div>