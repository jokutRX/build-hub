<template>
  <div class="supply-list">
    <!-- Скелетон/Лоадер -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Загрузка заявок...</p>
    </div>

    <!-- Пустое состояние -->
    <div v-else-if="visibleRequests.length === 0" class="empty-state">
      <p class="empty-title">Заявки не найдены</p>
      <p class="empty-sub">Нет позиций, соответствующих выбранным фильтрам</p>
    </div>

    <!-- Список элементов -->
    <div v-else class="list-items">
      <div 
        v-for="item in visibleRequests" 
        :key="item.id" 
        :class="['request-card', { expanded: expandedIds.has(item.id) }]"
      >
        <!-- Шапка карточки (видимая часть) -->
        <div class="card-header" @click="toggleExpand(item.id)">
          <div class="main-info">
            <!-- Стрелочка индикатор раскрытия -->
            <span class="chevron-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </span>
            <span class="material-title">{{ item.title }}</span>
            <span class="object-badge">{{ item.site || item.object }}</span>
          </div>

          <div class="meta-info" @click.stop>
            <!-- Количество со склонением -->
            <div class="quantity-tag">
              <span class="amount">{{ item.quantity || item.amount }}</span>
              <span class="unit">{{ pluralizeUnit(item.quantity || item.amount, item.unit) }}</span>
            </div>

            <!-- Статус приоритета -->
            <span :class="['priority-badge', getPriorityClass(item.priority)]">
              {{ getPriorityLabel(item.priority) }}
            </span>

            <!-- Кнопка удаления -->
            <button 
              class="btn-delete" 
              @click="$emit('request-delete', item)"
              title="Удалить заявку"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Выпадающий детальнее блок (Collapsible) -->
        <Transition name="expand">
          <div v-if="expandedIds.has(item.id)" class="card-details">
            <div class="details-grid">
              <div class="detail-item">
                <span class="label">Дата и время создания:</span>
                <span class="value">{{ formatDate(item.createdAt) }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Единица измерения:</span>
                <span class="value">{{ item.unit || 'шт' }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Объект снабжения:</span>
                <span class="value">{{ item.site || item.object }}</span>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  requests: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  pendingDeleteIds: { type: Array, default: () => [] }
})

defineEmits(['request-delete'])

// Храним ID раскрытых карточек
const expandedIds = ref(new Set())

const toggleExpand = (id) => {
  if (expandedIds.value.has(id)) {
    expandedIds.value.delete(id)
  } else {
    expandedIds.value.add(id)
  }
}

// Фильтруем заявки, скрывая те, что находятся в очереди на удаление
const visibleRequests = computed(() => {
  return props.requests.filter(item => !props.pendingDeleteIds.includes(item.id))
})

/* Вспомогательные функции для склонения и дат */
const pluralize = (number, one, two, five) => {
  let n = Math.abs(number) % 100
  if (n >= 5 && n <= 20) return five
  n %= 10
  if (n === 1) return one
  if (n >= 2 && n <= 4) return two
  return five
}

const pluralizeUnit = (count, unit) => {
  if (!unit) return ''
  const num = Number(count) || 0
  const normalized = unit.toLowerCase().trim()

  switch (normalized) {
    case 'тонны': case 'тонна': case 'т':
      return pluralize(num, 'тонна', 'тонны', 'тонн')
    case 'шт': case 'штука': case 'штуки':
      return pluralize(num, 'штука', 'штуки', 'штук')
    case 'м': case 'метр': case 'метры':
      return pluralize(num, 'метр', 'метра', 'метров')
    case 'м²': case 'кв.м': return 'м²'
    case 'м³': case 'куб.м': return 'м³'
    default: return unit
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'Не указана'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return dateString

  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return `${day}.${month}.${year}, ${hours}:${minutes}`
}

const getPriorityClass = (priority) => {
  const p = (priority || '').toUpperCase()
  if (p === 'CRITICAL') return 'critical'
  if (p === 'MEDIUM') return 'medium'
  return 'low'
}

const getPriorityLabel = (priority) => {
  const p = (priority || '').toUpperCase()
  if (p === 'CRITICAL') return 'Критичный'
  if (p === 'MEDIUM') return 'Средний'
  return 'Низкий'
}
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.supply-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;

  .loading-state, .empty-state {
    background: #ffffff;
    border: 1px solid $border;
    border-radius: 12px;
    padding: 3rem 1.5rem;
    text-align: center;
    color: $text-muted;

    .empty-title {
      font-weight: 700;
      font-size: 1.1rem;
      color: $text-main;
      margin: 0 0 0.25rem 0;
    }
    .empty-sub { font-size: 0.85rem; margin: 0; }
  }

  .spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #e2e8f0;
    border-top-color: $primary;
    border-radius: 50%;
    margin: 0 auto 0.75rem auto;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin { to { transform: rotate(360deg); } }

  .list-items {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
  }

  .request-card {
    background: #ffffff;
    border: 1px solid $border;
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;

    &:hover {
      border-color: color-mix(in srgb, $primary 30%, $border);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    &.expanded {
      border-color: $primary;
      .chevron-icon { transform: rotate(90deg); }
    }

    .card-header {
      padding: 0.85rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      cursor: pointer;
      user-select: none;

      .main-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;

        .chevron-icon {
          width: 18px;
          height: 18px;
          color: $text-muted;
          display: flex;
          align-items: center;
          transition: transform 0.2s ease;
          
          svg { width: 100%; height: 100%; }
        }

        .material-title {
          font-weight: 800;
          font-size: 0.95rem;
          color: $text-main;
        }

        .object-badge {
          background: #f1f5f9;
          color: $text-muted;
          font-size: 0.75rem;
          font-weight: 600;
          padding: 0.25rem 0.6rem;
          border-radius: 6px;
        }
      }

      .meta-info {
        display: flex;
        align-items: center;
        gap: 1rem;

        .quantity-tag {
          font-size: 0.9rem;
          display: flex;
          align-items: baseline;
          gap: 0.3rem;

          .amount { font-weight: 800; color: $text-main; }
          .unit { font-weight: 600; color: $text-muted; font-size: 0.85rem; }
        }

        .priority-badge {
          font-size: 0.75rem;
          font-weight: 700;
          padding: 0.25rem 0.65rem;
          border-radius: 12px;

          &.low { background: #f0fdf4; color: #16a34a; }
          &.medium { background: #fefce8; color: #d97706; }
          &.critical { background: #fef2f2; color: #dc2626; }
        }

        .btn-delete {
          background: transparent;
          border: none;
          color: #94a3b8;
          font-size: 1rem;
          cursor: pointer;
          padding: 0.2rem 0.4rem;
          border-radius: 4px;
          transition: all 0.2s;

          &:hover {
            color: #dc2626;
            background: #fef2f2;
          }
        }
      }
    }

    /* Раскрывающийся блок */
    .card-details {
      background: #fafafa;
      border-top: 1px solid #f1f5f9;
      padding: 1rem 1.25rem 1rem 2.8rem;

      .details-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;

        .detail-item {
          display: flex;
          flex-direction: column;
          gap: 0.2rem;

          .label {
            font-size: 0.75rem;
            color: $text-muted;
            font-weight: 600;
          }

          .value {
            font-size: 0.85rem;
            color: $text-main;
            font-weight: 700;
          }
        }
      }
    }
  }
}

/* Анимация раскрытия аккордеона */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 200px;
  opacity: 1;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
  overflow: hidden;
}
</style>