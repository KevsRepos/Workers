<script lang="ts">
import { getContext } from "svelte";
import { STEPS_CONTEXT, STEPS_ITEM_CONTEXT, type StepsContext } from "./context";

let { class: className = "", children }: { class?: string; children?: () => any } = $props();

const steps = getContext(STEPS_CONTEXT) as StepsContext;
const item = getContext(STEPS_ITEM_CONTEXT) as { index: number };

const active = $derived(steps.getStep() === item.index);
const completed = $derived(steps.getStep() > item.index);
</script>

<span
    class={className || "inline-flex h-7 w-7 items-center justify-center rounded-full border text-sm"}
    data-active={active}
    data-completed={completed}
>
    {@render children?.()}
</span>
