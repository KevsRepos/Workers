import StepsRoot from "./Steps.svelte";
import StepsList from "./StepsList.svelte";
import StepsItem from "./StepsItem.svelte";
import StepsTrigger from "./StepsTrigger.svelte";
import StepsIndicator from "./StepsIndicator.svelte";
import StepsSeparator from "./StepsSeparator.svelte";
import StepsContent from "./StepsContent.svelte";
import StepsPrevTrigger from "./StepsPrevTrigger.svelte";
import StepsNextTrigger from "./StepsNextTrigger.svelte";

const Steps = Object.assign(StepsRoot, {
	List: StepsList,
	Item: StepsItem,
	Trigger: StepsTrigger,
	Indicator: StepsIndicator,
	Separator: StepsSeparator,
	Content: StepsContent,
	PrevTrigger: StepsPrevTrigger,
	NextTrigger: StepsNextTrigger,
});

export default Steps;

export {
	Steps,
	StepsRoot,
	StepsList,
	StepsItem,
	StepsTrigger,
	StepsIndicator,
	StepsSeparator,
	StepsContent,
	StepsPrevTrigger,
	StepsNextTrigger,
};
