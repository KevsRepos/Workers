<script>
import { X } from "@lucide/svelte";
import { slide } from "svelte/transition";

let { close, title, body } = $props();
</script>

<div transition:slide class="fixed top-full -translate-y-full inset-0 z-50 bg-surface-100 text-surface-950 flex flex-col w-full h-4/6 rounded-2xl">
    <div class="flex justify-between p-4 border-b">
        <div class="font-bold">{@render title()}</div>

        <button onclick={close}>
            <X />
        </button>
    </div>

    <div class="p-2">
        {@render body()}
    </div>
</div>