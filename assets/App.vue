<!-- src/App.vue -->
<template>
  <div class="app-layout">
    <AppSidebar />
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import AppSidebar from './components/AppSidebar.vue'
</script>

<style lang="scss" scoped>

.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--color-bg-main); 

  .main-content {
    flex: 1;
    padding: var(--space-8) var(--space-10) var(--space-16);
    overflow-y: auto;
  }
}
</style>
