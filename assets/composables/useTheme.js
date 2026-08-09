/**
 * Theme Composable - Управление светлой/темной темой и цветовыми пресетами
 * Сохраняет выбор в localStorage, учитывает системную настройку
 */
import { ref, computed, watch, onMounted } from 'vue'

const THEME_KEY = 'snippet-vault-theme'
const COLOR_THEME_KEY = 'snippet-vault-color-theme'
const THEME_ATTR = 'data-theme'
const COLOR_THEME_ATTR = 'data-color-theme'

// Состояние темы: 'light' | 'dark' | 'system'
const themeMode = ref('system')
// Вычисленная тема: 'light' | 'dark'
const resolvedTheme = ref('light')

// Цветовая тема: 'zinc' | 'rose' | 'green' | 'orange' | 'violet' | 'blue'
const colorTheme = ref('zinc')

const COLOR_PRESETS = [
  { id: 'zinc', name: 'Цинк', color: '#71717a', darkColor: '#a1a1aa' },
  { id: 'rose', name: 'Роза', color: '#f43f5e', darkColor: '#fb7185' },
  { id: 'green', name: 'Зеленый', color: '#10b981', darkColor: '#34d399' },
  { id: 'orange', name: 'Оранжевый', color: '#f97316', darkColor: '#fb923c' },
  { id: 'violet', name: 'Сиреневый', color: '#8b5cf6', darkColor: '#a78bfa' },
  { id: 'blue', name: 'Синий', color: '#3b82f6', darkColor: '#60a5fa' },
]

/**
 * Получить системную тему
 */
function getSystemTheme() {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

/**
 * Применить тему и цветовой пресет к document.documentElement
 */
function applyTheme(theme, color) {
  if (typeof document === 'undefined') return
  const root = document.documentElement
  
  root.classList.add('theme-switching')
  
  if (theme === 'dark') {
    root.setAttribute(THEME_ATTR, 'dark')
  } else {
    root.removeAttribute(THEME_ATTR)
  }
  
  resolvedTheme.value = theme

  if (color) {
    root.setAttribute(COLOR_THEME_ATTR, color)
    colorTheme.value = color
  }

  requestAnimationFrame(() => {
    root.classList.remove('theme-switching')
  })
}

/**
 * Инициализация темы при загрузке
 */
function initTheme() {
  const savedTheme = localStorage.getItem(THEME_KEY)
  if (savedTheme && ['light', 'dark', 'system'].includes(savedTheme)) {
    themeMode.value = savedTheme
  }

  const savedColor = localStorage.getItem(COLOR_THEME_KEY)
  if (savedColor && COLOR_PRESETS.some(p => p.id === savedColor)) {
    colorTheme.value = savedColor
  }
  
  const theme = themeMode.value === 'system' ? getSystemTheme() : themeMode.value
  applyTheme(theme, colorTheme.value)
  
  if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', (e) => {
      if (themeMode.value === 'system') {
        applyTheme(e.matches ? 'dark' : 'light', colorTheme.value)
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
  applyTheme(theme, colorTheme.value)
}

/**
 * Установить цветовую тему
 */
function setColorTheme(color) {
  if (!COLOR_PRESETS.some(p => p.id === color)) return
  
  colorTheme.value = color
  localStorage.setItem(COLOR_THEME_KEY, color)
  applyTheme(resolvedTheme.value, color)
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

const isDark = computed(() => resolvedTheme.value === 'dark')

onMounted(initTheme)

export function useTheme() {
  return {
    themeMode,
    resolvedTheme,
    colorTheme,
    COLOR_PRESETS,
    isDark,
    setThemeMode,
    setColorTheme,
    toggleTheme,
    initTheme,
  }
}