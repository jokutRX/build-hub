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
@use "./styles/main.scss" as *;

.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc; 

  .main-content {
    flex: 1;
    padding: 2rem 2.5rem 4rem;
    overflow-y: auto;
  }
}
</style>