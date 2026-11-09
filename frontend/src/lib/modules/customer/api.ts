import { fetchApi } from "$lib/api/fetchApi";

export const searchCustomers = (query: string) =>
    fetchApi(`customers/search?customerQuery=${encodeURIComponent(query)}`, "GET");

export const getCustomer = (customerId: string) =>
    fetchApi(`customer/${encodeURIComponent(customerId)}`, "GET");

export const createCustomer = (customer: object) =>
    fetchApi("customers", "POST", customer);

export const createCustomerAddress = (customerId: string, address: object) =>
    fetchApi(`customer-address/${encodeURIComponent(customerId)}`, "POST", address);

export const getCustomerAddresses = (customerId: string) =>
    fetchApi(`customers/${encodeURIComponent(customerId)}/addresses`, "GET");
