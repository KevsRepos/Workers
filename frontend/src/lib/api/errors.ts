/**
 * RFC 9457 problem details as returned by the backend.
 */
export interface Problem {
	type: string;
	title: string;
	status: number;
	detail: string;
	instance?: string;
	requestId?: string;
	errors?: FieldError[];
}

export interface FieldError {
	field: string;
	message: string;
	code?: string;
}

export class ApiError extends Error {
	constructor(
		public readonly status: number,
		public readonly problem: Problem
	) {
		super(problem.detail || problem.title);
		this.name = 'ApiError';
	}

	get requestId(): string | undefined {
		return this.problem.requestId;
	}

	get type(): string {
		return this.problem.type.replace('urn:workers:problem:', '');
	}

	/**
	 * Field errors keyed by property path, e.g. { "lines[0].quantity": "…" }.
	 */
	get fieldErrors(): Record<string, string> {
		const result: Record<string, string> = {};
		for (const error of this.problem.errors ?? []) {
			result[error.field] ??= translate(error.message);
		}
		return result;
	}
}

export const isProblem = (value: unknown): value is Problem =>
	typeof value === 'object' &&
	value !== null &&
	typeof (value as Problem).status === 'number' &&
	typeof (value as Problem).title === 'string';

/**
 * Builds an ApiError from a fetch response (problem+json or anything else).
 */
export const errorFromResponse = async (response: Response): Promise<ApiError> => {
	let body: unknown = null;
	try {
		body = await response.json();
	} catch {
		// non-JSON error body
	}

	if (isProblem(body)) {
		return new ApiError(response.status, body);
	}

	return new ApiError(response.status, {
		type: 'urn:workers:problem:http-' + response.status,
		title: response.statusText || 'Fehler',
		status: response.status,
		detail: defaultMessage(response.status),
		requestId: response.headers.get('x-request-id') ?? undefined
	});
};

export const defaultMessage = (status: number): string => {
	if (status === 0) return 'Keine Verbindung zum Server. Bitte Netzwerk prüfen.';
	if (status === 401) return 'Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.';
	if (status === 403) return 'Dafür fehlt dir die Berechtigung.';
	if (status === 404) return 'Der Datensatz wurde nicht gefunden.';
	if (status === 409) return 'Der Datensatz wurde zwischenzeitlich geändert oder steht im Konflikt.';
	if (status === 422) return 'Bitte prüfe deine Eingaben.';
	if (status === 429) return 'Zu viele Anfragen. Bitte warte einen Moment.';
	if (status >= 500) return 'Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es erneut.';
	return 'Die Anfrage konnte nicht verarbeitet werden.';
};

/**
 * User facing German message for an error, including the support reference.
 */
export const userMessage = (error: unknown): string => {
	if (error instanceof ApiError) {
		const base = error.status >= 500 ? defaultMessage(error.status) : translate(error.message);
		return error.requestId && error.status >= 500 ? `${base} (Referenz: ${error.requestId.slice(0, 8)})` : base;
	}
	if (error instanceof TypeError) {
		return defaultMessage(0);
	}
	return defaultMessage(500);
};

const TRANSLATIONS: Array<[RegExp, string]> = [
	[/^This value should not be blank\.?$/i, 'Pflichtfeld.'],
	[/^This value is not a valid email address\.?$/i, 'Keine gültige E-Mail-Adresse.'],
	[/^This value should be positive\.?$/i, 'Muss größer als 0 sein.'],
	[/^This value should be either positive or zero\.?$/i, 'Darf nicht negativ sein.'],
	[/^This value is too long/i, 'Eingabe ist zu lang.'],
	[/^This value is not a valid date\.?$/i, 'Kein gültiges Datum.'],
	[/^Provide either first name and surname or a company name\.?$/i, 'Vor- und Nachname oder Firmenname angeben.'],
	[/^First name is required\.?$/i, 'Vorname ist erforderlich.'],
	[/^Surname is required\.?$/i, 'Nachname ist erforderlich.'],
	[/^Company name is required for companies\.?$/i, 'Firmenname ist erforderlich.'],
	[
		/^The pickup date must not be before the delivery date\.?$/i,
		'Das Abholdatum darf nicht vor dem Lieferdatum liegen.'
	],
	[/^Add at least one product\.?$/i, 'Mindestens einen Artikel hinzufügen.'],
	[/^A delivery requires a shipping address\.?$/i, 'Für eine Lieferung wird eine Lieferadresse benötigt.'],
	[/^The address does not belong to this customer\.?$/i, 'Die Adresse gehört nicht zu diesem Kunden.'],
	[
		/^Working time cannot be recorded for future days\.?$/i,
		'Arbeitszeit kann nicht für zukünftige Tage erfasst werden.'
	],
	[/^The end time must differ from the start time\.?$/i, 'Beginn und Ende dürfen nicht gleich sein.'],
	[/^The working period exceeds 16 hours/i, 'Mehr als 16 Stunden – bitte Beginn und Ende prüfen.'],
	[/^The break must be shorter than the working period\.?$/i, 'Die Pause muss kürzer als die Arbeitszeit sein.'],
	[/^There is already an entry for this day/i, 'Für diesen Tag gibt es bereits einen Eintrag.'],
	[/^This day does not exist in the selected month\.?$/i, 'Diesen Tag gibt es im gewählten Monat nicht.'],
	[/^Start time is required\.?$/i, 'Beginn ist erforderlich.'],
	[/^End time is required\.?$/i, 'Ende ist erforderlich.'],
	[/^Use the format HH:MM\.?$/i, 'Format HH:MM verwenden.'],
	[/^Password must be at least (\d+) characters long\.?$/i, 'Das Passwort muss mindestens $1 Zeichen lang sein.'],
	[/^Password is too weak/i, 'Das Passwort ist zu schwach. Nutze eine längere Passphrase.'],
	[/^Passwords do not match\.?$/i, 'Die Passwörter stimmen nicht überein.'],
	[/^The current password is incorrect\.?$/i, 'Das aktuelle Passwort ist falsch.'],
	[/^The new password must differ/i, 'Das neue Passwort muss sich vom aktuellen unterscheiden.'],
	[/^An account with this email address already exists\.?$/i, 'Es gibt bereits ein Konto mit dieser E-Mail-Adresse.'],
	[/^You cannot remove your own administrator rights/i, 'Du kannst dir selbst keine Administratorrechte entziehen.'],
	[/^At least one active administrator must remain\.?$/i, 'Es muss mindestens ein aktiver Administrator bleiben.'],
	[/^A product must be sellable, rentable or both\.?$/i, 'Ein Artikel muss verkäuflich und/oder vermietbar sein.'],
	[/^Products already belong to another union: (.*)$/i, 'Artikel gehören bereits zu einem anderen Verbund: $1'],
	[/^A product union must contain at least (\d+) products\.?$/i, 'Ein Verbund braucht mindestens $1 Artikel.'],
	[/^Select at least one product\.?$/i, 'Mindestens einen Artikel auswählen.'],
	[/^Full crates cannot exceed the total returned\.?$/i, 'Volle Kisten können nicht mehr sein als zurückgegebene.'],
	[
		/^Full bottles cannot exceed the total returned bottles\.?$/i,
		'Volle Flaschen können nicht mehr sein als zurückgegebene.'
	],
	[
		/cannot be removed because returns were already recorded/i,
		'Die Position kann nicht entfernt werden, weil bereits eine Rückgabe erfasst ist.'
	],
	[
		/cannot be changed because returns were already recorded/i,
		'Der Artikel kann nicht geändert werden, weil bereits eine Rückgabe erfasst ist.'
	],
	[
		/^The customer is referenced by delivery notes/i,
		'Der Kunde hat Lieferscheine mit gesetzlicher Aufbewahrungspflicht und kann noch nicht anonymisiert werden.'
	],
	[/^The customer has been anonymised/i, 'Der Kunde wurde anonymisiert und kann nicht mehr geändert werden.'],
	[/^Anonymised customers cannot be used\.?$/i, 'Anonymisierte Kunden können nicht verwendet werden.'],
	[/^(.*) not found\.?$/i, 'Der Datensatz wurde nicht gefunden.'],
	[/^The request contains invalid data\.?$/i, 'Bitte prüfe die markierten Felder.']
];

/**
 * Translates known backend messages into German; unknown messages are returned unchanged.
 */
export const translate = (message: string): string => {
	for (const [pattern, replacement] of TRANSLATIONS) {
		if (pattern.test(message)) {
			return message.replace(pattern, replacement);
		}
	}
	return message;
};
