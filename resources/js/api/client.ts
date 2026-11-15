const BASE_URL = '/api/v1';

export class ApiError extends Error {
    readonly status: number;

    constructor(status: number, message: string) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
    }
}

export async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const response = await fetch(`${BASE_URL}${path}`, {
        ...init,
        headers: {
            Accept: 'application/json',
            ...init.headers,
        },
    });

    if (!response.ok) {
        throw new ApiError(response.status, response.statusText);
    }

    return (await response.json()) as T;
}

export const api = {
    get: <T>(path: string): Promise<T> => request<T>(path),
};