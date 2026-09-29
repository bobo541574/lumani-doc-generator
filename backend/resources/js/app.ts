import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import router from '@/router';
import App from '@/App.vue';

const app = createApp({
    render: () => h(App),
});

app.use(createPinia());
app.use(router);

app.mount('#app');
