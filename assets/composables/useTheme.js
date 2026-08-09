/**
 * Theme Composable - Управление светлой/темной темой
 * Сохраняет выбор в localStorage, учитывает системную настройку
 */
import { ref, computed, watch, onMounted } from 'vue'

const THEME_KEY = 'snippet-vault-theme'
const THEME_ATTR = 'data-theme'

// Состояние темы: 'light' | 'dark' | 'system'
const themeMode = ref('system')
// Вычисленная тема: 'light' | 'dark'
const resolvedTheme = ref('light')
// Флаг переключения (для отключения переходов)
const isSwitching = ref(false)

/**
 * Получить системную тему
 */
function getSystemTheme() {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

/**
 * Применить тему к document.documentElement
 */
function applyTheme(theme) {
  const root = document.documentElement
  
  // Временно отключаем переходы
  root.classList.add('theme-switching')
  
  if (theme === 'dark') {
    root.setAttribute(THEME_ATTR, 'dark')
  } else {
    root.removeAttribute(THEME_ATTR)
  }
  
  resolvedTheme.value = theme
  
  // Включаем переходы обратно после следующего кадра
  requestAnimationFrame(() => {
    root.classList.remove('theme-switching')
  })
}

/**
 * Инициализация темы при загрузке
 */
function initTheme() {
  // Читаем сохранённую тему
  const saved = localStorage.getItem(THEME_KEY)
  if (saved && ['light', 'dark', 'system'].includes(saved)) {
    themeMode.value = saved
  }
  
  // Вычисляем и применяем
  const theme = themeMode.value === 'system' ? getSystemTheme() : themeMode.value
  applyTheme(theme)
  
  // Слушаем системную тему
  if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', (e) => {
      if (themeMode.value === 'system') {
        applyTheme(e.matches ? 'dark' : 'light')
      }
    })
  }
}

/**
 * Установить режим темы
 */
function setThemeMode(mode) {
  if (!['light', 'dark', 'system'].includes(mode)) return
  
  themeMode.value = mode
  localStorage.setItem(THEME_KEY, mode)
  
  const theme = mode === 'system' ? getSystemTheme() : mode
  applyTheme(theme)
}

/**
 * Переключить тему (light ↔ dark)
 */
function toggleTheme() {
  const modes = ['light', 'dark']
  const currentIndex = modes.indexOf(resolvedTheme.value)
  const nextMode = modes[(currentIndex + 1) % modes.length]
  setThemeMode(nextMode)
}

/**
 * Проверить, тёмная ли тема сейчас
 */
const isDark = computed(() => resolvedTheme.value === 'dark')

// Auto-init
onMounted(initTheme)

// Export
export function useTheme() {
  return {
    themeMode,
    resolvedTheme,
    isDark,
    isSwitching,
    setThemeMode,
    toggleTheme,
    initTheme,
  }
}

// Для использования вне setup()
export const theme = {
  get mode() { return themeMode.value },
  get resolved() { return resolvedTheme.value },
  get isDark() { return resolvedTheme.value === 'dark' },
  setMode: setThemeMode,
  toggle: toggleTheme,
  init: initTheme,
}