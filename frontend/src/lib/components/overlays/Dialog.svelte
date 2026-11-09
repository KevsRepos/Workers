<script lang="ts">
import { swipeGesture } from "$lib/attachments/swipeGesture";
import { Minus, X } from "@lucide/svelte";
import type { Snippet } from "svelte";
import { fade, scale, slide } from "svelte/transition";

let { close, title, body }: {
    close: () => any;
    title: Snippet;
    body: Snippet;
} = $props();

let open = $state(true);

let header: HTMLDivElement | null = $state(null);
</script>

<svelte:window onkeyup={(evt) => {evt.key === 'Escape' && (close())}} />

<!-- svelte-ignore a11y_click_events_have_key_events -->
<!-- svelte-ignore a11y_no_static_element_interactions -->
<div onclick={(event) => {event.target === event.currentTarget && (close())}} class="hidden md:flex fixed z-999 top-0 bottom-0 left-0 right-0 bg-surface-950-50/20" transition:fade={{ duration: 150 }}>
    <div class="card bg-surface-50-950 w-[70vw] h-fit relative top-[15vh] left-1/2 -translate-x-[50%] px-4 space-y-4 shadow-xl" transition:scale={{ duration: 150, start: 0.9 }}>
        <header class="flex items-center justify-between border-b pt-4 pb-2 mb-0">
            <div class="font-bold">{@render title()}</div>
            <button class="btn btn-ghost btn-sm self-end" onclick={() => (close())}>
                <X />
            </button>
        </header>
        <main class="py-2">
            {@render body()}
        </main>
    </div>
</div>

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
    class="flex md:hidden fixed inset-x-0 bottom-0 z-50 bg-surface-100 text-surface-950 flex-col w-full h-4/6 rounded-2xl"
>
    <div bind:this={header} class="flex flex-col items-center">
        <Minus />
        <header class="flex justify-between px-4 pb-4 border-b w-full">
            <div class="font-bold flex items-center">
                {@render title()}
            </div>

            <button onclick={close}>
                <X />
            </button>
        </header>
    </div>

    <div class="p-2">
        {@render body()}
    </div>
</div>