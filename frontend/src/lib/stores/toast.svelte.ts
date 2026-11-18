export type ToastTone = 'success' | 'error' | 'info';

export interface Toast {
	id: number;
	tone: ToastTone;
	message: string;
}

class ToastStore {
	items = $state<Toast[]>([]);
	private nextId = 1;

	show(message: string, tone: ToastTone = 'info', durationMs = tone === 'error' ? 8000 : 4000): void {
		const id = this.nextId++;
		this.items.push({ id, tone, message });
		setTimeout(() => this.dismiss(id), durationMs);
	}

	success(message: string): void {
		this.show(message, 'success');
	}

	error(message: string): void {
		this.show(message, 'error');
	}

	dismiss(id: number): void {
		this.items = this.items.filter((t) => t.id !== id);
	}
}

export const toast = new ToastStore();
