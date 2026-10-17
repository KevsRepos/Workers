<script lang="ts">
import { getContext } from "svelte";
import { STEPS_CONTEXT, type StepsContext } from "./context";

let {
    index,
    class: className = "",
    children,
}: {
    index: number;
    class?: string;
    children?: () => any;
} = $props();

const steps = getContext(STEPS_CONTEXT) as StepsContext;
const visible = $derived(steps.getStep() === index);
</script>

{#if visible}
    <div class={className}>
        {@render children?.()}
    </div>
{/if}
