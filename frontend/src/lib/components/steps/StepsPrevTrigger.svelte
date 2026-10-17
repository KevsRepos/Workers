<script lang="ts">
import { getContext } from "svelte";
import { STEPS_CONTEXT, type StepsContext } from "./context";

let { class: className = "", children }: { class?: string; children?: () => any } = $props();

const steps = getContext(STEPS_CONTEXT) as StepsContext;
const disabled = $derived(steps.getStep() <= 0);
</script>

<button type="button" class={className} disabled={disabled} onclick={() => steps.prev()}>
    {@render children?.()}
</button>
