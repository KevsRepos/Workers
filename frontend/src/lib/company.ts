import { env } from '$env/dynamic/public';

/** Company data for printed documents, configured via PUBLIC_COMPANY_* environment variables. */
export const company = () => ({
	name: env.PUBLIC_COMPANY_NAME ?? '',
	street: env.PUBLIC_COMPANY_STREET ?? '',
	postalCode: env.PUBLIC_COMPANY_POSTAL_CODE ?? '',
	city: env.PUBLIC_COMPANY_CITY ?? '',
	phone: env.PUBLIC_COMPANY_PHONE ?? '',
	fax: env.PUBLIC_COMPANY_FAX ?? '',
	email: env.PUBLIC_COMPANY_EMAIL ?? ''
});
