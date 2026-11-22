import { t } from './i18n.js';
/** Jedyne miejsce w projekcie, które woła fetch(). */
export class ApiError extends Error {
    constructor(status, code, message) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.code = code;
    }
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export async function request(method, url, { body } = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-Token': csrfToken() };
    const options = { method, headers, credentials: 'same-origin' };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(url, options);
    } catch (error) {
        throw new ApiError(0, 'network', t('js.api.network'));
    }

    let payload = null;
    try {
        payload = await response.json();
    } catch (error) {
        payload = null;
    }

    if (!response.ok) {
        const info = payload?.error ?? {};
        throw new ApiError(
            response.status,
            info.code ?? 'error',
            info.message ?? t('js.api.server_error', { status: response.status })
        );
    }

    return payload;
}
