<script setup>
import { ref, onMounted } from 'vue'
import { supplyApi } from './api/supplyApi.js'
import SupplyForm from './components/SupplyForm.vue'
import SupplyList from './components/SupplyList.vue'

const requests = ref([])
const loading = ref(false)

const loadRequests = async () => {
  loading.value = true
  try {
    requests.value = await supplyApi.getAll()
  } catch (err) {
    console.error('Ошибка при загрузке заявок:', err)
  } finally {
    loading.value = false
  }
}

const handleCreate = async (newRequestData) => {
  try {
    await supplyApi.create(newRequestData)
    await loadRequests()
  } catch (err) {
    alert('Не удалось сохранить заявку. Проверьте соединение с бэкендом.')
  }
}

const handleDelete = async (id) => {
  try {
    await supplyApi.delete(id)
    await loadRequests()
  } catch (err) {
    alert('Не удалось удалить заявку')
  }
}

onMounted(loadRequests)
</script>

<template>
  <div class="app-layout">
    <header class="app-header">
      <div class="header-container">
        <div class="brand">
          <div class="logo">🏗️</div>
          <div>
            <h1>ProcureLog <span class="env-tag">Enterprise</span></h1>
            <p>Система автоматизации снабжения и учета строительных материалов</p>
          </div>
        </div>
      </div>
    </header>

    <main class="main-content">
      <SupplyForm @create="handleCreate" />
      <SupplyList 
        :requests="requests" 
        :loading="loading" 
        @delete-request="handleDelete" 
      />
    </main>
  </div>
</template>

<style lang="scss">
@use "./styles/main.scss" as *;

.app-layout {
  min-height: 100vh;

  .app-header {
    background: $surface;
    border-bottom: 1px solid $border;
    padding: 1.25rem 0;
    margin-bottom: 2.5rem;

    .header-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 1.5rem;

      .brand {
        display: flex;
        align-items: center;
        gap: 1rem;

        .logo { 
          font-size: 2.2rem; 
          line-height: 1;
        }

        h1 {
          margin: 0;
          font-size: 1.35rem;
          font-weight: 800;
          color: $text-main;
          display: flex;
          align-items: center;
          gap: 0.6rem;

          .env-tag {
            font-size: 0.65rem;
            text-transform: uppercase;
            background: $primary-light;
            color: $primary;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 700;
            letter-spacing: 0.05em;
          }
        }

        p { 
          margin: 0.2rem 0 0 0; 
          font-size: 0.85rem; 
          color: $text-muted; 
        }
      }
    }
  }

  .main-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1.5rem 4rem;
  }
}
</style>