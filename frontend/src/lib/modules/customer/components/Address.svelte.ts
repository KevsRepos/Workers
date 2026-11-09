export class Address {
    street: string = $state("");
    houseNumber: string = $state("");
    postalCode: string = $state("");
    city: string = $state("");
    standardShippingAddress: boolean = $state(false);
    standardBillingAddress: boolean = $state(false);

    validity = $state<boolean>(false);

    constructor(street: string, houseNumber: string, postalCode: string, city: string, standardShippingAddress: boolean = false, standardBillingAddress: boolean = false) {
        this.street = street;
        this.houseNumber = houseNumber;
        this.postalCode = postalCode;
        this.city = city;
        this.standardShippingAddress = standardShippingAddress;
        this.standardBillingAddress = standardBillingAddress;

        this.validity = false;
    }
}