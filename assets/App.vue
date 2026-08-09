<!-- src/App.vue -->
<template>
  <div class="app-layout">
    <AppHeader @toggle-sidebar="toggleSidebar" />
    <div class="app-body">
      <AppSidebar ref="sidebarRef" />
      <main class="main-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'

const sidebarRef = ref(null)

const toggleSidebar = () => {
  if (sidebarRef.value) {
    sidebarRef.value.toggleSidebar()
  }
}
</script>

<style lang="scss" scoped>

.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: var(--color-bg-main); 

  .app-body {
    display: flex;
    flex: 1;
  }

  .main-content {
    flex: 1;
    padding: var(--space-8) var(--space-10) var(--space-16);
    overflow-y: auto;
  }
}
</style>
