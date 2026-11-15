import { requestWorkers } from "$lib/api/requestWorkers";

export const searchCustomers = (query: string) =>
    requestWorkers(`customers/search?customerQuery=${encodeURIComponent(query)}`, "GET");

export const getCustomer = (customerId: string) =>
    requestWorkers(`customer/${encodeURIComponent(customerId)}`, "GET");

export const createCustomer = (customer: object) =>
    requestWorkers("customers", "POST", customer);

export const createCustomerAddress = (customerId: string, address: object) =>
    requestWorkers(`customer-address/${encodeURIComponent(customerId)}`, "POST", address);

export const getCustomerAddresses = (customerId: string) =>
    requestWorkers(`customers/${encodeURIComponent(customerId)}/addresses`, "GET");
