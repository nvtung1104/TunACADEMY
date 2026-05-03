import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

import '../css/app.css'

// Laravel Echo — connects to Reverb WebSocket
window.Pusher = Pusher

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'
const isTLS = reverbScheme === 'https' || window.location.protocol === 'https:'

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: isTLS,
    enabledTransports: isTLS ? ['wss'] : ['ws'],
    disableStats: true,
})

// Suppress reconnect errors in console (Reverb might not be running in dev)
window.Echo.connector.pusher.connection.bind('error', () => {})
window.Echo.connector.pusher.connection.bind('failed', () => {})

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.mount('#app')
