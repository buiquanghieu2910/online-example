import { defineStore } from 'pinia';

const THEME_KEY = 'online-exam-theme';

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export const useThemeStore = defineStore('theme', {
    state: () => ({
        preference: 'system',
        currentTheme: 'light',
    }),
    getters: {
        isDark: (state) => state.currentTheme === 'dark',
    },
    actions: {
        initTheme() {
            const savedPreference = localStorage.getItem(THEME_KEY);
            this.preference = ['light', 'dark', 'system'].includes(savedPreference) ? savedPreference : 'system';

            this.applyTheme();

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.preference === 'system') {
                    this.applyTheme();
                }
            });
        },
        applyTheme() {
            this.currentTheme = this.preference === 'system' ? getSystemTheme() : this.preference;
            document.documentElement.classList.toggle('dark', this.currentTheme === 'dark');
        },
        toggleTheme() {
            this.preference = this.currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem(THEME_KEY, this.preference);
            this.applyTheme();
        },
    },
});

