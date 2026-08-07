<template>
  <aside :class="['app-sidebar', { collapsed: isCollapsed }]">
    <!-- Header / Brand -->
    <div class="sidebar-header">
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
import { ref } from 'vue'

const isCollapsed = ref(false)
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.app-sidebar {
  width: 250px;
  min-height: 100vh;
  /* Светлый фирменный фон, чуть выделенный относительно основной области */
  background: #ffffff;
  border-right: 1px solid $border;
  display: flex;
  flex-direction: column;
  padding: 1.25rem 0.85rem;
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: sticky;
  top: 0;
  box-sizing: border-box;

  &.collapsed {
    width: 72px;

    .sidebar-header {
      justify-content: center;
      padding: 0;
    }

    .nav-item {
      justify-content: center;
      padding: 0.65rem 0;
    }

    .toggle-btn {
      align-self: center;
    }
  }

  /* 1. ЛОГОТИП И ШАПКА */
  .sidebar-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    padding: 0 0.5rem;
    height: 32px;

    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      color: $primary;

      .logo-icon {
        width: 24px;
        height: 24px;
      }
    }

    .brand-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      white-space: nowrap; /* Фикс переноса текста */

      .title {
        font-weight: 800;
        font-size: 1.1rem;
        color: $text-main;
        letter-spacing: -0.02em;
      }

      .env-tag {
        font-size: 0.6rem;
        text-transform: uppercase;
        background: $primary-light;
        color: $primary;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        font-weight: 700;
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
      color: $text-muted;
      letter-spacing: 0.08em;
      margin-bottom: 0.5rem;
      padding-left: 0.5rem;
      white-space: nowrap;
      overflow: hidden;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 0.75rem;
      border-radius: 8px;
      color: #475569;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.15s ease;
      white-space: nowrap; /* КЛЮЧЕВОЙ ФИКС: Запрещает скачки текста в 2 строки */
      overflow: hidden;    /* Прячет вылезающий текст при схлопывании */

      .nav-icon {
        width: 20px;
        height: 20px;
        min-width: 20px; /* Чтобы иконка не сжималась */
        stroke: #64748b;
        transition: stroke 0.15s ease;
      }

      .label {
        white-space: nowrap;
      }

      &:hover {
        background: #f1f5f9;
        color: $text-main;

        .nav-icon {
          stroke: $text-main;
        }
      }

      /* Активное состояние маршрута */
      &.router-link-active {
        background: $primary;
        color: #ffffff;

        .nav-icon {
          stroke: #ffffff;
        }
      }
    }
  }

  /* 3. КНОПКА СВЕРНУТЬ */
  .toggle-btn {
    background: transparent;
    border: 1px solid $border;
    color: $text-muted;
    border-radius: 6px;
    padding: 0.4rem;
    cursor: pointer;
    align-self: flex-end;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;

    .toggle-icon {
      width: 16px;
      height: 16px;
      transition: transform 0.25s ease;

      &.rotated {
        transform: rotate(180deg);
      }
    }

    &:hover {
      background: #f8fafc;
      color: $text-main;
      border-color: #cbd5e1;
    }
  }
}
</style>