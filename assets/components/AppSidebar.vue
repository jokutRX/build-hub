<template>
  <aside :class="['app-sidebar', { collapsed: isCollapsed }]">
    <!-- Header / Brand -->
    <div class="sidebar-header">
      <div class="brand-wrapper">
        <transition name="fade">
          <span v-if="!isCollapsed" class="menu-label">Меню</span>
        </transition>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="nav-group">
        <span v-if="!isCollapsed" class="group-title">СНАБЖЕНИЕ</span>
        
        <router-link to="/requests" class="nav-item" title="Заявки на закупку">
          <ShoppingCart class="nav-icon" :size="20" />
          <span v-if="!isCollapsed" class="label">Заявки</span>
        </router-link>
      </div>
    </nav>

    <!-- Toggle Button -->
    <button class="toggle-btn" @click="toggleSidebar">
      <ChevronsLeft class="toggle-icon" :class="{ rotated: isCollapsed }" :size="16" />
    </button>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { ChevronsLeft, ShoppingCart } from 'lucide-vue-next'

const isCollapsed = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

defineExpose({ toggleSidebar })
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
    flex-direction: column;
    height: 40px;
    margin-bottom: var(--space-8);
    padding: 0 var(--space-2);

    .brand-wrapper {
      display: flex;
      align-items: center;
      height: 100%;

      .menu-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--color-text-muted);
        letter-spacing: 0.1em;
        white-space: nowrap;
      }
    }
  }

  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
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
        color: var(--color-text-muted);
        transition: color var(--transition-fast);
      }

      .label {
        white-space: nowrap;
      }

      &:hover {
        background: var(--color-bg-secondary);
        color: var(--color-text-main);

        .nav-icon {
          color: var(--color-text-main);
        }
      }

      &.router-link-active {
        background: var(--color-primary);
        color: var(--color-primary-contrast);

        .nav-icon {
          color: var(--color-primary-contrast);
        }
      }
    }
  }

  /* 3. КНОПКА СВЕРНУТЬ */
  .toggle-btn {
    position: absolute;
    bottom: var(--space-5);
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    color: var(--color-text-muted);
    border-radius: var(--radius-full);
    padding: var(--space-2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
    z-index: 10;
    box-shadow: var(--shadow-sm);

    .toggle-icon {
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
