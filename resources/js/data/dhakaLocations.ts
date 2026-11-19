export interface NamedLocation {
    name: string;
    latitude: number;
    longitude: number;
}

export const dhakaLocations: NamedLocation[] = [
    { name: 'Gulshan', latitude: 23.7925, longitude: 90.4078 },
    { name: 'Dhanmondi', latitude: 23.7465, longitude: 90.376 },
    { name: 'Uttara', latitude: 23.8759, longitude: 90.3795 },
    { name: 'Mirpur', latitude: 23.8223, longitude: 90.3654 },
    { name: 'Motijheel', latitude: 23.733, longitude: 90.4172 },
];

export const storeTypes: string[] = ['Electronics', 'Grocery', 'Pharmacy', 'Bookshop', 'Cafe'];