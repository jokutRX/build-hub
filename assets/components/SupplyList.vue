<script setup>
import { ref, computed } from 'vue'
import SupplyItem from './SupplyItem.vue'

const props = defineProps({
  requests: { type: Array, required: true },
  loading: { type: Boolean, default: false }
})

const emit = defineEmits(['delete-request'])

const selectedSiteFilter = ref('ALL')

const filteredRequests = computed(() => {
  if (selectedSiteFilter.value === 'ALL') return props.requests
  return props.requests.filter(r => r.site === selectedSiteFilter.value)
})
</script>

<template>
  <div class="list-section">
    <div class="list-header">
      <div class="title-group">
        <h2>Реестр заявок</h2>
        <span class="count-badge">{{ filteredRequests.length }} позиций</span>
      </div>

      <div class="filter-box">
        <label>Площадка:</label>
        <select v-model="selectedSiteFilter">
          <option value="ALL">Все объекты</option>
          <option value="ЖК Северный">ЖК Северный</option>
          <option value="ЖК Невский">ЖК Невский</option>
          <option value="БЦ Горизонт">БЦ Горизонт</option>
        </select>
      </div>
    </div>

    <!-- State Loading -->
    <div v-if="loading" class="state-container">
      <div class="spinner"></div>
      <p>Загрузка реестра снабжения...</p>
    </div>

    <!-- State Empty -->
    <div v-else-if="filteredRequests.length === 0" class="state-container empty">
      <div class="empty-icon">📂</div>
      <h3>Заявок не найдено</h3>
      <p>На выбранном объекте пока нет активных позиций на закупку</p>
    </div>

    <!-- Cards Grid -->
    <div v-else class="cards-grid">
      <SupplyItem 
        v-for="item in filteredRequests" 
        :key="item.id" 
        :request="item"
        @delete="emit('delete-request', $event)" 
      />
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.list-section {
  .list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;

    .title-group { 
      display: flex; 
      align-items: center; 
      gap: 0.75rem; 

      h2 { margin: 0; font-size: 1.25rem; font-weight: 700; color: $text-main; }
      
      .count-badge {
        background: $primary-light;
        color: $primary;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
      }
    }

    .filter-box {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      color: $text-muted;

      select { 
        @include input-base; 
        width: auto; 
        padding: 0.4rem 0.75rem; 
      }
    }
  }

  .cards-grid {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
  }

  .state-container {
    background: $surface;
    border: 1px dashed $border;
    border-radius: 12px;
    padding: 3.5rem 1rem;
    text-align: center;
    color: $text-muted;

    .empty-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
    h3 { margin: 0; font-size: 1.1rem; color: $text-main; }
    p { margin: 0.25rem 0 0 0; font-size: 0.875rem; }

    .spinner {
      width: 32px;
      height: 32px;
      border: 3px solid $border;
      border-top-color: $primary;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin: 0 auto 1rem;
    }
  }
}

@keyframes spin { 
  to { transform: rotate(360deg); } 
}
</style>