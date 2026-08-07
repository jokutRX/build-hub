<!-- assets/App.vue -->
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

<style lang="scss">
@use "./styles/main.scss" as *;

.app-layout {
  display: flex;
  min-height: 100vh;
  /* Заменили переменную на прямую/стандартную для проекта */
  background-color: #f8fafc; 

  .main-content {
    flex: 1;
    padding: 2rem 2.5rem 4rem;
    overflow-y: auto;
  }
}
</style>