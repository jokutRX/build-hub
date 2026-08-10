<template>
  <div class="supply-list-container">
    <!-- Лоадер -->
    <div v-if="loading" class="list-skeleton">
      <div class="skeleton-card" v-for="i in 3" :key="i"></div>
    </div>

    <!-- Пустое состояние -->
    <div v-else-if="!requests || requests.length === 0" class="empty-state">
      <p class="empty-title">Заявок не найдено</p>
      <p class="empty-sub">Попробуйте изменить выбранную дату или параметры фильтра</p>
    </div>

    <!-- Список карточек -->
    <div v-else class="list-wrapper">
        <SupplyItem 
          v-for="item in requests" 
          :key="item.id" 
          :item="item" 
          :isPendingDelete="pendingDeleteIds.includes(item.id)"
          @delete="$emit('request-delete', $event)" 
          @duplicate="$emit('duplicate', $event)"
        />

    </div>
  </div>
</template>

<script setup>
import SupplyItem from './SupplyItem.vue'

defineProps({
  requests: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pendingDeleteIds: {
    type: Array,
    default: () => []
  }
})

defineEmits(['request-delete', 'duplicate'])
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.supply-list-container {
  .list-wrapper {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
  }

  .empty-state {
    text-align: center;
    padding: var(--space-12) var(--space-6);
    // border: 1px solid var(--color-border); // Убрана граница
    // border-radius: var(--radius-lg); // Убрано скругление
    color: var(--color-text-main); // Основной цвет

    .empty-title {
      font-weight: 800;
      font-size: 1.15rem;
      margin-bottom: var(--space-2);
    }

    .empty-sub {
      font-size: 0.9rem;
      color: var(--color-text-muted);
    }
  }

  .list-skeleton {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);

    .skeleton-card {
      height: 58px;
      background: linear-gradient(90deg, var(--color-bg-secondary) 25%, var(--color-border) 50%, var(--color-bg-secondary) 75%);
      background-size: 200% 100%;
      border-radius: var(--radius-lg);
      animation: skeleton-shimmer 1.5s infinite;
    }
  }
}

@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
