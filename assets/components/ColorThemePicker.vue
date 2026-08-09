<template>
  <div class="color-picker-wrapper" ref="pickerRef">
    <button 
      class="picker-toggle-btn" 
      @click="isOpen = !isOpen"
      title="Выбрать цветовую тему"
      aria-label="Выбрать цветовую тему"
    >
      <svg class="picker-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
        <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
        <path d="M2 2l7.586 7.586"></path>
        <circle cx="11" cy="11" r="2"></circle>
      </svg>
    </button>

    <Transition name="dropdown">
      <div v-if="isOpen" class="picker-dropdown">
        <div class="dropdown-header">Цветовая палитра</div>
        <div class="presets-grid">
          <button
            v-for="preset in COLOR_PRESETS"
            :key="preset.id"
            :class="['preset-item', { active: colorTheme === preset.id }]"
            @click="selectPreset(preset.id)"
          >
            <span 
              class="preset-circle" 
              :style="{ background: isDark ? preset.darkColor : preset.color }"
            ></span>
            <span class="preset-name">{{ preset.name }}</span>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useTheme } from '../composables/useTheme.js'

const { colorTheme, setColorTheme, COLOR_PRESETS, isDark } = useTheme()
const isOpen = ref(false)
const pickerRef = ref(null)

const selectPreset = (id) => {
  setColorTheme(id)
  isOpen.value = false
}

const handleClickOutside = (e) => {
  if (pickerRef.value && !pickerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.color-picker-wrapper {
  position: relative;
  display: inline-block;

  .picker-toggle-btn {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-text-secondary);
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--transition-fast);

    .picker-icon {
      width: 18px;
      height: 18px;
      stroke: currentColor;
    }

    &:hover {
      background: var(--color-bg-secondary);
      border-color: var(--color-border-strong);
      color: var(--color-text-main);
    }
  }

  .picker-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 240px;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: var(--space-3);
    z-index: var(--z-dropdown);

    .dropdown-header {
      font-size: 0.7rem;
      font-weight: 800;
      color: var(--color-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: var(--space-2);
      padding-left: var(--space-1);
    }

    .presets-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: var(--space-2);

      .preset-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--space-1);
        padding: var(--space-2);
        background: var(--color-bg-secondary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition-fast);

        .preset-circle {
          width: 24px;
          height: 24px;
          border-radius: 50%;
          border: 2px solid rgba(255, 255, 255, 0.2);
          box-shadow: var(--shadow-xs);
        }

        .preset-name {
          font-size: 0.7rem;
          font-weight: 600;
          color: var(--color-text-secondary);
          white-space: nowrap;
        }

        &:hover {
          background: var(--color-surface-hover);
          border-color: var(--color-border-strong);
          
          .preset-name {
            color: var(--color-text-main);
          }
        }

        &.active {
          border-color: var(--color-primary);
          background: var(--color-primary-light);

          .preset-name {
            color: var(--color-primary);
            font-weight: 700;
          }
        }
      }
    }
  }
}

/* Анимация dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>