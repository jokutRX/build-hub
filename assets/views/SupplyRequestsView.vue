<template>
  <div class="page-container">
    <header class="page-header">
      <h1>Заявки на закупку</h1>
      <p>Управление потребностями объектов и автоматизация снабжения</p>
    </header>

    <SupplyForm @create="handleCreate" />
    
    <SupplyList 
      :requests="requests" 
      :loading="loading" 
      @delete-request="handleDelete" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { supplyApi } from '../api/supplyApi.js'
import SupplyForm from '../components/SupplyForm.vue'
import SupplyList from '../components/SupplyList.vue'

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

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.page-container {
  max-width: 900px;
  margin: 0 auto;

  .page-header {
    margin-bottom: 2rem;

    h1 {
      font-size: 1.5rem;
      font-weight: 800;
      color: $text-main;
      margin: 0 0 0.25rem 0;
    }

    p {
      color: $text-muted;
      font-size: 0.9rem;
      margin: 0;
    }
  }
}
</style>