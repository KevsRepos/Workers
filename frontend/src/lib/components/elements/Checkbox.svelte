<script lang="ts">
import { Check } from "@lucide/svelte";

let { label, checked = $bindable(), inputClasses = '', ...restProps } = $props();
</script>

<label class="w-full flex justify-between bg-surface-50-950 rounded-xl border border-surface-200-800 items-center gap-2 {restProps.class}">
    <span class="p-2">{label}</span>
    <input {...restProps} class="peer relative hidden" type="checkbox" bind:checked={checked} />
    <span class="border border-surface-200-800 peer-checked:border-0 peer-checked:bg-primary-500 p-1 mr-1 h-full rounded-xl">
        <Check color="white" size="16" />
    </span>
</label>