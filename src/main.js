import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { lazikan } from './utils/gambar'

const app = createApp(App)

app.directive('lazikan', lazikan)

app.use(createPinia())
app.use(router)

app.mount('#app')
