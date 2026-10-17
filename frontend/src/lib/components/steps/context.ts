export const STEPS_CONTEXT = Symbol("steps-context");
export const STEPS_ITEM_CONTEXT = Symbol("steps-item-context");

export type StepsOrientation = "horizontal" | "vertical";

export interface StepsContext {
    getStep: () => number;
    getCount: () => number;
    getLinear: () => boolean;
    setStep: (next: number, force?: boolean) => void;
    next: () => void;
    prev: () => void;
}
