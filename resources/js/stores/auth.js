import { defineStore } from 'pinia';
import api from '../services/api';
import { refreshCsrfToken } from '../services/api';

const roleHomeMap = {
    admin: '/app/admin/dashboard',
    teacher: '/app/teacher/dashboard',
    student: '/app/student/dashboard',
};

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoaded: false,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.user),
        roleHome: (state) => roleHomeMap[state.user?.role] || '/app/login',
    },
    actions: {
        async fetchMe() {
            try {
                const { data } = await api.get('/auth/me');
                this.user = data.data;
            } catch {
                this.user = null;
            } finally {
                this.isLoaded = true;
            }
        },
        async login(credentials) {
            await refreshCsrfToken();
            const { data } = await api.post('/auth/login', credentials);
            this.user = data.data.user;
            return data.data.home;
        },
        async logout() {
            await refreshCsrfToken();
            await api.post('/auth/logout');
            this.user = null;
            this.isLoaded = true;
        },
    },
});



