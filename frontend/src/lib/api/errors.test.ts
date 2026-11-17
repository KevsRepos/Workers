import { describe, expect, it } from 'vitest';
import { ApiError, errorFromResponse, translate, userMessage } from './errors';

describe('api errors', () => {
	it('parses problem details and field errors', async () => {
		const response = new Response(
			JSON.stringify({
				type: 'urn:workers:problem:validation-failed',
				title: 'Validation failed',
				status: 422,
				detail: 'x',
				requestId: 'r1',
				errors: [{ field: 'email', message: 'This value is not a valid email address.' }]
			}),
			{ status: 422, headers: { 'Content-Type': 'application/problem+json' } }
		);
		const error = await errorFromResponse(response);
		expect(error).toBeInstanceOf(ApiError);
		expect(error.type).toBe('validation-failed');
		expect(error.fieldErrors).toEqual({ email: 'Keine gültige E-Mail-Adresse.' });
	});

	it('handles non-JSON errors', async () => {
		const error = await errorFromResponse(new Response('<html>', { status: 502 }));
		expect(error.status).toBe(502);
		expect(userMessage(error)).toContain('unerwarteter Fehler');
	});

	it('never shows internal details for server errors and includes the reference', () => {
		const error = new ApiError(500, {
			type: 't',
			title: 'x',
			status: 500,
			detail: 'SQLSTATE[HY000]',
			requestId: 'abcdef1234'
		});
		expect(userMessage(error)).not.toContain('SQLSTATE');
		expect(userMessage(error)).toContain('abcdef12');
	});

	it('translates known messages', () => {
		expect(translate('Password must be at least 12 characters long.')).toBe(
			'Das Passwort muss mindestens 12 Zeichen lang sein.'
		);
		expect(translate('something else')).toBe('something else');
	});
});
