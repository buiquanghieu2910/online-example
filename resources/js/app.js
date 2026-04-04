import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

import App from './App.vue';
import router from './router';
import { useThemeStore } from './stores/theme';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
	theme: {
		preset: Aura,
		options: {
			darkModeSelector: '.dark',
		},
	},
});
app.use(ToastService);

const themeStore = useThemeStore(pinia);
themeStore.initTheme();

app.mount('#app');
