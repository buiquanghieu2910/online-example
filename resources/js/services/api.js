import axios from 'axios';

const AUTH_EXPIRED_MESSAGE_KEY = 'auth-expired-message';
let isRedirectingToLogin = false;

export async function refreshCsrfToken() {
    await axios.get('/csrf-token', {
        withCredentials: true,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
    withCredentials: true,
});

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        if (error.response?.status === 419 && originalRequest && !originalRequest.__isCsrfRetry) {
            originalRequest.__isCsrfRetry = true;
            await refreshCsrfToken();
            return api(originalRequest);
        }

        if (error.response?.status === 401) {
            const currentPath = window.location.pathname;

            if (currentPath !== '/app/login' && !isRedirectingToLogin) {
                isRedirectingToLogin = true;
                sessionStorage.setItem(AUTH_EXPIRED_MESSAGE_KEY, 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.');
                window.location.assign('/app/login');
            }
        }

        return Promise.reject(error);
    }
);

export default api;

