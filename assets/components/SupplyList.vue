<!-- assets/components/SupplyList.vue -->
<template>
  <div class="supply-list">
    <div v-if="loading" class="loading-state">
      Загрузка позиций...
    </div>

    <div v-else-if="requests.length === 0" class="empty-state">
      <div class="empty-icon">📁</div>
      <h3>Заявок не найдено</h3>
      <p>На выбранную дату или параметры фильтра нет активных позиций</p>
    </div>

    <div v-else class="list-items">
      <SupplyItem 
        v-for="item in requests" 
        :key="item.id" 
        :request="item" 
        @delete="$emit('delete-request', $event)" 
      />
    </div>
  </div>
</template>

<script setup>
import SupplyItem from './SupplyItem.vue'

defineProps({
  requests: Array,
  loading: Boolean
})

defineEmits(['delete-request'])
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  background: #ffffff;
  border: 1px dashed $border;
  border-radius: 12px;

  .empty-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
  h3 { font-size: 1.1rem; font-weight: 700; color: $text-main; margin: 0 0 0.25rem 0; }
  p { font-size: 0.85rem; color: $text-muted; margin: 0; }
}

.loading-state {
  text-align: center;
  padding: 2rem;
  color: $text-muted;
}
</style>