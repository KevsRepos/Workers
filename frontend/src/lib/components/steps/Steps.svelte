<script lang="ts">
import { setContext } from "svelte";
import { STEPS_CONTEXT, type StepsContext, type StepsOrientation } from "./context";

let {
	step = $bindable(0),
	count = 0,
	orientation = "horizontal",
	linear = false,
	class: className = "",
	children,
	onStepChange,
}: {
	step?: number;
	count?: number;
	orientation?: StepsOrientation;
	linear?: boolean;
	class?: string;
	children?: () => any;
	onStepChange?: (details: { step: number }) => void;
} = $props();

const maxStep = () => Math.max(0, count - 1);

const setStep = (next: number, force = false) => {
	const clamped = Math.min(Math.max(0, next), maxStep());

	if (!force && linear && clamped > step + 1) {
		return;
	}

	if (clamped === step) {
		return;
	}

	step = clamped;
	onStepChange?.({ step: clamped });
};

const ctx: StepsContext = {
	getStep: () => step,
	getCount: () => count,
	getLinear: () => linear,
	setStep,
	next: () => setStep(step + 1),
	prev: () => setStep(step - 1),
};

setContext(STEPS_CONTEXT, ctx);
</script>

<div class={className} data-orientation={orientation}>
	{@render children?.()}
</div>