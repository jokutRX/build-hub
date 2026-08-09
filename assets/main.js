import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useTheme } from './composables/useTheme.js'
import './styles/main.scss'

// Инициализируем тему до монтирования приложения
const theme = useTheme()
theme.initTheme()

const app = createApp(App)

app.use(router)
app.mount('#app')
