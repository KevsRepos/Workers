/** DTOs of the backend API (see backend/src/Modules/**\/Dto). */

export interface Page<T> {
	items: T[];
	page: number;
	limit: number;
	total: number;
	pages: number;
}

export type Role = 'ROLE_USER' | 'ROLE_ADMIN';

export interface Account {
	id: string;
	emailAddress: string;
	firstName: string;
	surname: string;
	displayName: string;
	roles: Role[];
	active: boolean;
	lastLoginAt: string | null;
	createdAt: string | null;
	updatedAt: string | null;
}

export interface TokenPair {
	accessToken: string;
	accessTokenExpiresAt: string;
	refreshToken: string;
	refreshTokenExpiresAt: string;
	account: Account;
}

export interface Address {
	id: string;
	street: string;
	houseNumber: string;
	postalCode: string;
	city: string;
	country: string | null;
	note: string | null;
	singleLine: string;
	defaultShipping: boolean;
	defaultBilling: boolean;
}

export interface AddressInput {
	street: string;
	houseNumber: string;
	postalCode: string;
	city: string;
	country: string | null;
	note: string | null;
	defaultShipping: boolean;
	defaultBilling: boolean;
}

export interface CustomerSummary {
	id: string;
	company: boolean;
	displayName: string;
	email: string | null;
	phone: string | null;
	defaultAddress: string | null;
}

export interface Customer {
	id: string;
	company: boolean;
	firstName: string | null;
	surname: string | null;
	companyName: string | null;
	displayName: string;
	email: string | null;
	phone: string | null;
	defaultShippingAddressId: string | null;
	defaultBillingAddressId: string | null;
	addresses: Address[];
	anonymized: boolean;
	createdAt: string | null;
	updatedAt: string | null;
}

export interface CustomerInput {
	company: boolean;
	firstName: string | null;
	surname: string | null;
	companyName: string | null;
	email: string | null;
	phone: string | null;
	addresses?: AddressInput[];
}

export interface Deposit {
	id: string;
	label: string | null;
	singleAmount: number;
	crateAmount: number | null;
}

export interface Product {
	id: string;
	name: string;
	salesPrice: number | null;
	sellable: boolean;
	rentable: boolean;
	quantityInCrate: number | null;
	deposit: Deposit | null;
	archived: boolean;
	createdAt: string | null;
	updatedAt: string | null;
}

export interface ProductInput {
	name: string;
	salesPrice: number | null;
	depositId: string | null;
	sellable: boolean;
	rentable: boolean;
	quantityInCrate: number | null;
	archived: boolean;
}

export type ProductFilter = 'all' | 'with-deposit' | 'without-deposit' | 'sellable' | 'rentable' | 'archived';

export interface ProductUnion {
	id: string;
	name: string;
	products: Product[];
}

export interface OrderPreset {
	id: string;
	name: string;
	products: Product[];
}

export enum DeliveryNoteStatus {
	Open = 1,
	Delivered = 2,
	Canceled = 3,
	Returned = 4,
	Completed = 5
}

export interface DeliveryNoteSummary {
	id: string;
	number: string;
	status: DeliveryNoteStatus;
	statusLabel: string;
	deliveryDate: string;
	pickupDate: string | null;
	delivery: boolean;
	customerId: string;
	customerName: string;
	shippingAddress: string | null;
	shortDescription: string | null;
	assignment: string | null;
	callBefore: boolean;
}

export interface ReturnEntry {
	id: string;
	returnedTotal: number | null;
	returnedTotalBottles: number | null;
	returnedFull: number | null;
	returnedFullBottles: number | null;
}

export interface DeliveryNoteLine {
	id: string;
	productId: string;
	productName: string;
	quantity: number | null;
	position: number;
	rentable: boolean;
	sellable: boolean;
	quantityInCrate: number | null;
	salesPrice: number | null;
	deposit: Deposit | null;
	returnEntry: ReturnEntry | null;
}

export interface DeliveryNote {
	id: string;
	number: string;
	status: DeliveryNoteStatus;
	statusLabel: string;
	deliveryDate: string;
	pickupDate: string | null;
	delivery: boolean;
	customer: CustomerSummary;
	shippingAddress: Address | null;
	billingAddress: Address | null;
	shortDescription: string | null;
	privateDescription: string | null;
	assignment: string | null;
	adultGuests: number | null;
	childGuests: number | null;
	callBefore: boolean;
	lines: DeliveryNoteLine[];
	createdBy: string | null;
	createdAt: string | null;
	updatedAt: string | null;
}

export interface DeliveryNoteLineInput {
	id: string | null;
	productId: string;
	quantity: number | null;
}

export interface DeliveryNoteInput {
	customerId: string;
	deliveryDate: string;
	pickupDate: string | null;
	delivery: boolean;
	shortDescription: string | null;
	privateDescription: string | null;
	assignment: string | null;
	shippingAddressId: string | null;
	billingAddressId: string | null;
	adultGuests: number | null;
	childGuests: number | null;
	callBefore: boolean;
	lines: DeliveryNoteLineInput[];
}

export interface ReturnGroup {
	key: string;
	name: string;
	isUnion: boolean;
	lineIds: string[];
	productNames: string[];
	quantity: number;
	rentable: boolean;
	quantityInCrate: number | null;
	deposit: Deposit | null;
	returnEntry: ReturnEntry | null;
}

export interface ReturnEntryInput {
	lineIds: string[];
	returnedTotal: number | null;
	returnedTotalBottles: number | null;
	returnedFull: number | null;
	returnedFullBottles: number | null;
}

export type TimeEntryType = 'work' | 'vacation' | 'sick_leave' | 'holiday';

export interface TimeEntry {
	id: string;
	day: number;
	date: string;
	type: TimeEntryType;
	typeLabel: string;
	start: string | null;
	end: string | null;
	breakMinutes: number | null;
	note: string | null;
	hours: number;
	warnings: string[];
	updatedAt: string | null;
}

export interface TimeEntryInput {
	day: number;
	type: TimeEntryType;
	start: string | null;
	end: string | null;
	breakMinutes: number | null;
	note: string | null;
}

export interface TimeSheetDay {
	day: number;
	date: string;
	weekday: number;
	weekend: boolean;
	future: boolean;
	entry: TimeEntry | null;
}

export interface TimeSheetMonth {
	id: string | null;
	year: number;
	month: number;
	accountId: string;
	accountName: string;
	days: TimeSheetDay[];
	workedHours: number;
	absenceHours: number;
	totalHours: number;
	countsByType: Record<TimeEntryType, number>;
	warningCount: number;
}

export interface MonthSummary {
	month: number;
	entries: number;
	totalHours: number;
}

export interface ReminderSettings {
	enabled: boolean;
	time: string;
	weekdays: number[];
}

export interface AuditEntry {
	seq: number;
	eventId: string;
	occurredAt: string;
	category: string;
	action: string;
	actorId: string | null;
	actorLabel: string | null;
	subjectType: string | null;
	subjectId: string | null;
	changes: Record<string, unknown> | null;
	context: Record<string, string> | null;
	hash: string;
}

export interface AuditVerification {
	valid: boolean;
	checked: number;
	firstSeq: number | null;
	lastSeq: number | null;
	headMatches: boolean;
	brokenAtSeq: number | null;
	reason: string | null;
}
