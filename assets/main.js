import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { theme } from './composables/useTheme.js'
import './styles/main.scss'

// Инициализируем тему до монтирования приложения
theme.init()

const app = createApp(App)

app.use(router)
app.mount('#app')
