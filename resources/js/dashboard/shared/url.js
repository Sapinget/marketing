const csrfHeader = () => {
    const cookie = document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='));
    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
};

export const resolveAppUrl = (url) => {
    if (!url || /^https?:\/\//i.test(url)) return url;
    if (window.MARKETING_BACKEND_URL) {
        return `${String(window.MARKETING_BACKEND_URL).replace(/\/+$/, '')}${url}`;
    }
    return url;
};

export const jsonApi = async (url, options = {}) => {
    const token = csrfHeader();
    const response = await fetch(resolveAppUrl(url), {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
            ...(options.headers || {})
        },
    });
    if (!response.ok) {
        if (response.status === 401) {
            const unauthorizedError = new Error('Sesi login berakhir. Silakan login kembali.');
            unauthorizedError.status = 401;
            throw unauthorizedError;
        }
        let payload = null;
        try { payload = await response.json(); } catch (error) { payload = null; }
        const errorMessages = payload && payload.errors && typeof payload.errors === 'object'
            ? Object.values(payload.errors).flat().filter(Boolean)
            : [];
        const message = errorMessages[0] || (payload && payload.message) || `HTTP ${response.status}`;
        const requestError = new Error(message);
        requestError.status = response.status;
        requestError.payload = payload;
        throw requestError;
    }
    return response.status === 204 ? null : response.json();
};
