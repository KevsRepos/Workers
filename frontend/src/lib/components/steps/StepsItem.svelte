<script lang="ts">
import { getContext, setContext } from "svelte";
import { STEPS_CONTEXT, STEPS_ITEM_CONTEXT, type StepsContext } from "./context";

let {
    index,
    class: className = "",
    children,
}: {
    index: number;
    class?: string;
    children?: () => any;
} = $props();

const steps = (getContext(STEPS_CONTEXT) as StepsContext);

setContext(STEPS_ITEM_CONTEXT, { index });

const isActive = $derived(steps.getStep() === index);
const isCompleted = $derived(steps.getStep() > index);
</script>

<div class={className} data-active={isActive} data-completed={isCompleted}>
    {@render children?.()}
</div>
