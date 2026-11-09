<script lang="ts">
import { CircleAlert, CircleCheck, CircleX, Info } from "@lucide/svelte";

const { title = '', message, type = 'info' }: { title?: string; message?: string; type?: 'info' | 'warning' | 'error' | 'success' } = $props();

let icons = {
    'info': Info,
    'warning': CircleAlert,
    'error': CircleX,
    'success': CircleCheck
}

let Icon = $derived(icons[type]);
</script>

<div class="lg:max-w-[70vw] mx-auto mb-4 w-full px-2 py-5 card preset-filled-warning-950-50 flex items-start gap-2">
    <Icon />
    <div>
        <h3 class="text-lg font-bold leading-[initial]">{title}</h3>
        <p class="pt-2">{message}</p>
    </div>
</div>