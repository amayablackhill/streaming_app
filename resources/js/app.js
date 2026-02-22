import './bootstrap';
import Alpine from 'alpinejs';

import { createApp } from 'vue';
import App from './src/App.vue';
import store from './src/store';

const app = createApp(App);
app.use(store);
app.mount('#app');

window.Alpine = Alpine;
Alpine.start();
