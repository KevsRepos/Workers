<script lang="ts">
import { getContext } from "svelte";
import { STEPS_CONTEXT, STEPS_ITEM_CONTEXT, type StepsContext } from "./context";

let { class: className = "", children }: { class?: string; children?: () => any } = $props();

const steps = getContext(STEPS_CONTEXT) as StepsContext;
const item = getContext(STEPS_ITEM_CONTEXT) as { index: number };

const active = $derived(steps.getStep() === item.index);
const completed = $derived(steps.getStep() > item.index);
</script>

<button
    type="button"
    class={className}
    data-active={active}
    data-completed={completed}
    onclick={() => steps.setStep(item.index)}
>
    {@render children?.()}
</button>
