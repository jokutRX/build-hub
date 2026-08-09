<template>
  <aside :class="['app-sidebar', { collapsed: isCollapsed }]">
    <!-- Header / Brand & Theme Switcher -->
    <div class="sidebar-header">
      <div class="brand-wrapper">
        <div class="brand-logo">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 20h20M5 20V8l7-5 7 5v12M9 20v-6h6v6" />
          </svg>
        </div>
        <div v-if="!isCollapsed" class="brand-info">
          <span class="title">BuildHub</span>
          <span class="env-tag">ERP</span>
        </div>
      </div>

      <!-- Theme Switcher next to logo -->
      <button 
        class="theme-switch-btn" 
        @click="toggleTheme"
        :title="themeTooltip"
        :aria-label="themeTooltip"
      >
        <svg v-if="!isDark" class="theme-icon sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5" />
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
        </svg>
        <svg v-else class="theme-icon moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </svg>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="nav-group">
        <span v-if="!isCollapsed" class="group-title">СНАБЖЕНИЕ</span>
        
        <router-link to="/requests" class="nav-item" title="Заявки на закупку">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 8L12 3 3 8v8l9 5 9-5V8z" />
            <path d="M3 8l9 5 9-5" />
            <path d="M12 13v9" />
          </svg>
          <span v-if="!isCollapsed" class="label">Заявки на закупку</span>
        </router-link>
      </div>
    </nav>

    <!-- Toggle Button -->
    <button class="toggle-btn" @click="isCollapsed = !isCollapsed" title="Свернуть/Развернуть">
      <svg class="toggle-icon" :class="{ rotated: isCollapsed }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M15 18l-6-6 6-6" />
      </svg>
    </button>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTheme } from '../composables/useTheme.js'

const { isDark, toggleTheme } = useTheme()

const isCollapsed = ref(false)

const themeTooltip = computed(() => isDark.value ? 'Переключить на светлую тему' : 'Переключить на тёмную тему')
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.app-sidebar {
  width: 250px;
  min-height: 100vh;
  background: var(--color-surface);
  border-right: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  padding: var(--space-5) var(--space-3);
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: sticky;
  top: 0;
  box-sizing: border-box;

  &.collapsed {
    width: 72px;

    .sidebar-header {
      flex-direction: column;
      gap: var(--space-2);
      align-items: center;
      height: auto;
      padding: 0;
    }

    .nav-item {
      justify-content: center;
      padding: var(--space-2) 0;
    }

    .toggle-btn {
      align-self: center;
    }
  }

  /* 1. ЛОГОТИП И ШАПКА */
  .sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-8);
    padding: 0 var(--space-1);
    height: 32px;

    .brand-wrapper {
      display: flex;
      align-items: center;
      gap: var(--space-3);
    }

    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--color-primary);

      .logo-icon {
        width: 24px;
        height: 24px;
      }
    }

    .brand-info {
      display: flex;
      align-items: center;
      gap: var(--space-2);
      white-space: nowrap;

      .title {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--color-text-main);
        letter-spacing: -0.02em;
      }

      .env-tag {
        font-size: 0.6rem;
        text-transform: uppercase;
        background: var(--color-primary-light);
        color: var(--color-primary);
        padding: var(--space-1) var(--space-1);
        border-radius: var(--radius-xs);
        font-weight: 700;
      }
    }

    .theme-switch-btn {
      background: transparent;
      border: 1px solid var(--color-border);
      color: var(--color-text-secondary);
      width: 32px;
      height: 32px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      transition: all var(--transition-fast);

      .theme-icon {
        width: 18px;
        height: 18px;
      }

      &:hover {
        background: var(--color-bg-secondary);
        border-color: var(--color-border-strong);
        color: var(--color-text-main);
      }

      &:focus-visible {
        outline: none;
        box-shadow: 0 0 0 2px var(--color-primary-light);
      }
    }
  }

  /* 2. НАВИГАЦИЯ */
  .sidebar-nav {
    flex: 1;

    .group-title {
      display: block;
      font-size: 0.65rem;
      font-weight: 800;
      color: var(--color-text-muted);
      letter-spacing: 0.08em;
      margin-bottom: var(--space-2);
      padding-left: var(--space-2);
      white-space: nowrap;
      overflow: hidden;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: var(--space-3);
      padding: var(--space-2) var(--space-3);
      border-radius: var(--radius-md);
      color: var(--color-text-secondary);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all var(--transition-fast);
      white-space: nowrap;
      overflow: hidden;

      .nav-icon {
        width: 20px;
        height: 20px;
        min-width: 20px;
        stroke: var(--color-text-muted);
        transition: stroke var(--transition-fast);
      }

      .label {
        white-space: nowrap;
      }

      &:hover {
        background: var(--color-bg-secondary);
        color: var(--color-text-main);

        .nav-icon {
          stroke: var(--color-text-main);
        }
      }

      &.router-link-active {
        background: var(--color-primary);
        color: var(--color-primary-contrast);

        .nav-icon {
          stroke: var(--color-primary-contrast);
        }
      }
    }
  }

  /* 3. КНОПКА СВЕРНУТЬ */
  .toggle-btn {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-text-muted);
    border-radius: var(--radius-sm);
    padding: var(--space-1);
    cursor: pointer;
    align-self: flex-end;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);

    .toggle-icon {
      width: 16px;
      height: 16px;
      transition: transform var(--transition-base);

      &.rotated {
        transform: rotate(180deg);
      }
    }

    &:hover {
      background: var(--color-bg-secondary);
      color: var(--color-text-main);
      border-color: var(--color-border-strong);
    }
  }
}
</style>
