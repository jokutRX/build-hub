<template>
  <header class="app-header">
    <div class="header-left">
      <h2 class="logo">BuildHub</h2>
      <button class="menu-btn" @click="$emit('toggle-sidebar')" title="Меню">
        <span class="icon">≡</span>
      </button>
    </div>
    <div class="header-right">
      <button class="icon-btn" @click="toggleTheme" title="Переключить тему">
        <span class="icon">{{ isDark ? '◓' : '◒' }}</span>
      </button>

      <div class="color-picker-container" ref="pickerRef">
        <button class="icon-btn" @click="isOpen = !isOpen" title="Цветовая схема">
          <span class="icon">⌗</span>
        </button>
        <div v-if="isOpen" class="color-dropdown">
          <p class="dropdown-title">Выберите цветовую тему</p>
          <div class="grid-container">
            <label v-for="preset in COLOR_PRESETS" :key="preset.id" class="color-option">
              <input 
                type="radio" 
                :value="preset.id" 
                v-model="colorTheme"
                @change="setColorTheme(preset.id)"
                class="hidden-radio"
              >
              <div class="swatch-wrapper" :class="{ 'active': colorTheme === preset.id }">
                <span class="swatch" :style="{ backgroundColor: preset.color }"></span>
              </div>
              <span class="label-text">{{ preset.name }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useTheme } from '../composables/useTheme'

const { isDark, toggleTheme, colorTheme, setColorTheme, COLOR_PRESETS } = useTheme()
const isOpen = ref(false)
const pickerRef = ref(null)

const closePicker = (e) => {
  if (pickerRef.value && !pickerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', closePicker))
onUnmounted(() => document.removeEventListener('click', closePicker))
</script>

<style lang="scss" scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5rem;
  height: var(--header-height);
  background-color: var(--color-surface);
  border-bottom: 1px solid var(--color-border);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.menu-btn {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  width: 36px;
  height: 36px;
  cursor: pointer;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
  color: var(--color-text-main);
  font-size: 1.25rem;
  font-weight: 800;

  &:hover {
    background-color: var(--color-bg-tertiary);
    border-color: var(--color-border-strong);
  }
}

.logo {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-text-main);
}

.header-right {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.icon-btn {
  background: transparent;
  border: 1px solid var(--color-border);
  width: 36px;
  height: 36px;
  cursor: pointer;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
  color: var(--color-text-main);

  &:hover {
    background-color: var(--color-bg-secondary);
    border-color: var(--color-border-strong);
  }

  .icon {
    font-size: 1.25rem;
    line-height: 1;
  }
}

.color-picker-container {
  position: relative;
}

.color-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.75rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 1.25rem;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  width: 280px;
  z-index: var(--z-dropdown);
}

.dropdown-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-muted);
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.grid-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.color-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.hidden-radio {
  display: none;
}

.swatch-wrapper {
  width: 52px;
  height: 52px;
  border-radius: var(--radius-sm);
  border: 2px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-secondary);
  transition: all var(--transition-fast);
}

.swatch {
  width: 20px;
  height: 20px;
  border-radius: 4px;
}

.label-text {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  font-weight: 500;
}
</style>
