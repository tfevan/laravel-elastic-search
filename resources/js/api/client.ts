const BASE_URL = '/api/v1';

export type ValidationErrors = Record<string, string[]>;

export class ApiError extends Error {
    readonly status: number;
    readonly errors: ValidationErrors;

    constructor(status: number, message: string, errors: ValidationErrors = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors;
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
        const body = (await response.json().catch(() => ({}))) as {
            message?: string;
            errors?: ValidationErrors;
        };

        throw new ApiError(response.status, body.message ?? response.statusText, body.errors);
    }

    return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
}

function withBody(method: string, body: unknown): RequestInit {
    return {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    };
}

export const api = {
    get: <T>(path: string): Promise<T> => request<T>(path),
    post: <T>(path: string, body: unknown): Promise<T> => request<T>(path, withBody('POST', body)),
    put: <T>(path: string, body: unknown): Promise<T> => request<T>(path, withBody('PUT', body)),
    delete: (path: string): Promise<void> => request<void>(path, { method: 'DELETE' }),
};