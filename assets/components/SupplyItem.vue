<!-- assets/components/SupplyItem.vue -->
<template>
  <div :class="['supply-item-card', { expanded: isExpanded }]">
    <!-- КРАТКИЙ ЗАГОЛОВОК (Кликабельная часть) -->
    <div class="card-summary" @click="isExpanded = !isExpanded">
      <div class="summary-left">
        <!-- Стрелка состояния -->
        <span :class="['chevron', { rotated: isExpanded }]">❯</span>
        
        <!-- Название позиции -->
        <span class="item-title">{{ request.title || request.name }}</span>
        
        <!-- Метка объекта -->
        <span class="object-badge">📍 {{ request.object || 'ЖК Северный' }}</span>
      </div>

      <div class="summary-right">
        <!-- Количество -->
        <span class="amount-tag">
          <strong>{{ request.amount || request.quantity }}</strong> {{ request.unit || 'шт' }}
        </span>

        <!-- Приоритет -->
        <span :class="['priority-badge', request.priority?.toLowerCase()]">
          {{ formatPriority(request.priority) }}
        </span>
      </div>
    </div>

    <!-- ПОДРОБНЫЙ РАЗВЕРНУТЫЙ БЛОК (Collapse) -->
    <div v-if="isExpanded" class="card-details">
      <div class="details-grid">
        <div class="detail-item">
          <span class="label">Дата создания:</span>
          <span class="value">{{ request.createdAt || 'Сегодня' }}</span>
        </div>
        <div class="detail-item">
          <span class="label">Площадка:</span>
          <span class="value">{{ request.object || 'Не указана' }}</span>
        </div>
        <div class="detail-item">
          <span class="label">Статус согласования:</span>
          <span class="status-value pending">⏳ В обработке</span>
        </div>
      </div>

      <!-- Действия -->
      <div class="details-actions">
        <button class="btn-delete" @click.stop="$emit('delete', request.id)">
          🗑️ Удалить позицию
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  request: {
    type: Object,
    required: true
  }
})

defineEmits(['delete'])

const isExpanded = ref(false)

const formatPriority = (priority) => {
  const map = {
    CRITICAL: 'Критичный',
    MEDIUM: 'Средний',
    LOW: 'Низкий'
  }
  return map[priority] || priority || 'Средний'
}
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.supply-item-card {
  background: #ffffff;
  border: 1px solid $border;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  transition: all 0.2s ease;

  &:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
  }

  &.expanded {
    border-color: $primary;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
  }

  /* Шапка карточки */
  .card-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    cursor: pointer;
    user-select: none;

    .summary-left {
      display: flex;
      align-items: center;
      gap: 0.85rem;

      .chevron {
        font-size: 0.75rem;
        color: $text-muted;
        transition: transform 0.2s ease;
        &.rotated { transform: rotate(90deg); color: $primary; }
      }

      .item-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: $text-main;
      }

      .object-badge {
        font-size: 0.75rem;
        color: $text-muted;
        background: #f8fafc;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid $border;
      }
    }

    .summary-right {
      display: flex;
      align-items: center;
      gap: 1rem;

      .amount-tag {
        font-size: 0.9rem;
        color: $text-main;
        strong { font-weight: 800; }
      }

      .priority-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.25rem 0.65rem;
        border-radius: 12px;

        &.critical { background: #fef2f2; color: #ef4444; }
        &.medium { background: #fefce8; color: #eab308; }
        &.low { background: #f0fdf4; color: #22c55e; }
      }
    }
  }

  /* Подробный развернутый блок */
  .card-details {
    padding: 1rem 1.25rem 1.25rem;
    background: #f8fafc;
    border-top: 1px dashed $border;

    .details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;

      .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;

        .label { font-size: 0.75rem; color: $text-muted; font-weight: 600; }
        .value { font-size: 0.85rem; color: $text-main; font-weight: 600; }
        .status-value { font-size: 0.85rem; font-weight: 700; }
      }
    }

    .details-actions {
      display: flex;
      justify-content: flex-end;

      .btn-delete {
        background: transparent;
        border: 1px solid #fca5a5;
        color: #ef4444;
        padding: 0.4rem 0.85rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;

        &:hover { background: #fef2f2; }
      }
    }
  }
}
</style>