<script lang="ts">
let { class: className = "" }: { class?: string } = $props();
</script>

<div class={className || "my-2 h-px w-full bg-surface-300-700"}></div>
