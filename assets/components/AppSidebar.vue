<template>
  <aside :class="['app-sidebar', { collapsed: isCollapsed }]">
    <!-- Header / Brand -->
    <div class="sidebar-header">
      <div class="brand-wrapper">
        <span class="menu-label">МЕНЮ</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <template v-for="(group, index) in navGroups" :key="group.title">
        <div class="nav-group">
          <span class="group-title">{{ group.title }}</span>
          
          <router-link 
            v-for="item in group.items" 
            :key="item.to" 
            :to="item.to" 
            class="nav-item" 
            :title="isCollapsed ? item.label : ''"
          >
            <component :is="item.icon" class="nav-icon" :size="20" />
            <span class="label">{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Разделительная черта между группами -->
        <hr v-if="index < navGroups.length - 1" class="nav-divider" />
      </template>
    </nav>

    <!-- Footer Version Info -->
    <div class="sidebar-footer">
      <span class="version-text">v1.0.0 — BuildHub ERP</span>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { 
  LayoutDashboard, 
  ShoppingCart, 
  FileText, 
  Users, 
  Boxes, 
  ArrowLeftRight, 
  Receipt, 
  Wallet, 
  Settings 
} from 'lucide-vue-next'

const isCollapsed = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

// Структура ERP-меню
const navGroups = [
  {
    title: 'ОПЕРАЦИИ',
    items: [
      { label: 'Главная', to: '/dashboard', icon: LayoutDashboard }
    ]
  },
  {
    title: 'СНАБЖЕНИЕ',
    items: [
      { label: 'Заявки', to: '/requests', icon: ShoppingCart },
      { label: 'Заказы поставщикам', to: '/purchase-orders', icon: FileText },
      { label: 'Поставщики', to: '/suppliers', icon: Users }
    ]
  },
  {
    title: 'СКЛАД И УЧЕТ',
    items: [
      { label: 'Остатки', to: '/inventory', icon: Boxes },
      { label: 'Перемещения', to: '/transfers', icon: ArrowLeftRight }
    ]
  },
  {
    title: 'БИЗНЕС И ФИНАНСЫ',
    items: [
      { label: 'Счета и Оплаты', to: '/invoices', icon: Receipt },
      { label: 'Расходы', to: '/expenses', icon: Wallet },
      { label: 'Настройки', to: '/settings', icon: Settings }
    ]
  }
]

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

  /* Элементы с плавным скрытием текста */
  .menu-label,
  .group-title,
  .label,
  .version-text {
    white-space: nowrap;
    overflow: hidden;
    transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), 
                max-width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 200px;
    opacity: 1;
  }

  /* Состояние СВЁРНУТО */
  &.collapsed {
    width: 72px;

    .sidebar-header {
      align-items: center;
      justify-content: center;
    }

    .nav-item {
      justify-content: center;
      padding: var(--space-2) 0;
    }

    /* Синхронно и плавно прячем текстовые блоки */
    .menu-label,
    .group-title,
    .label,
    .version-text {
      opacity: 0;
      max-width: 0;
      pointer-events: none;
    }

    .nav-divider {
      margin: var(--space-2) var(--space-2);
    }
  }

  /* 1. ШАПКА САЙДБАРА */
  .sidebar-header {
    display: flex;
    align-items: center;
    height: 32px;
    margin-bottom: var(--space-5);
    padding: 0 var(--space-2);

    .brand-wrapper {
      .menu-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--color-text-muted);
        letter-spacing: 0.12em;
      }
    }
  }

  /* 2. НАВИГАЦИЯ */
  .sidebar-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    overflow-y: auto;
    overflow-x: hidden;

    .group-title {
      display: block;
      font-size: 0.65rem;
      font-weight: 800;
      color: var(--color-text-muted);
      letter-spacing: 0.1em;
      margin-bottom: var(--space-2);
      padding-left: var(--space-2);
      text-transform: uppercase;
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
      transition: background var(--transition-fast), color var(--transition-fast);

      .nav-icon {
        width: 20px;
        height: 20px;
        min-width: 20px;
        color: var(--color-text-muted);
        transition: color var(--transition-fast);
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

    .nav-divider {
      border: none;
      border-top: 1px solid var(--color-border);
      margin: var(--space-2) 0;
      opacity: 0.6;
      transition: margin 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
  }

  /* 3. ВЕРСИЯ В ФУТЕРЕ */
  .sidebar-footer {
    margin-top: auto;
    padding-top: var(--space-4);
    display: flex;
    justify-content: center;
    align-items: center;

    .version-text {
      font-size: 0.725rem;
      font-weight: 500;
      color: var(--color-text-muted);
      letter-spacing: 0.03em;
      opacity: 0.8;
    }
  }
}
</style>