<template>
  <div :class="['supply-card', { expanded: isExpanded, 'pending-delete': isPendingDelete }]">
    <!-- Шапка карточки (Кликабельная) -->
    <div class="card-header" @click="toggleExpand">
      <div class="header-left">
        <span :class="['arrow-icon', { rotated: isExpanded }]">›</span>
        <h3 class="title">{{ item.title }}</h3>
        <span class="site-badge">{{ item.site || item.object }}</span>
      </div>

      <div class="header-right">
        <!-- Склонение количества и единиц (1 тонна / 100 тонн) -->
        <span class="quantity">{{ displayQuantity }}</span>

        <span :class="['priority-badge', item.priority?.toLowerCase()]">
          {{ formatPriority(item.priority) }}
        </span>
        <button class="btn-delete" title="Удалить" @click.stop="$emit('delete', item)">✕</button>
      </div>
    </div>

    <!-- Выпадающая часть (Collapsible) -->
    <Transition name="expand">
      <div v-if="isExpanded" class="card-details">
        <div class="details-grid">
          <div class="detail-item" v-if="item.deliveryTimeStart">
            <span class="label">Окно доставки:</span>
            <span class="value">
              {{ item.deliveryTimeStart }} — {{ item.deliveryTimeEnd || 'не указано' }}
            </span>
          </div>

          <div class="detail-item" v-if="item.unloadingEquipment !== undefined && item.unloadingEquipment !== null">
            <span class="label">Разгрузочная техника:</span>
            <span class="value">
              {{ displayUnloading }}
            </span>
          </div>

          <!-- Форматирование даты в дд.мм.гггг -->
          <div class="detail-item" v-if="item.createdAt || item.date">
            <span class="label">Дата:</span>
            <span class="value">{{ formattedDate }}</span>
          </div>
        </div>

        <!-- БЛОК РАСЧЕТОВ БЭКЕНДА (SupplyCalculationService) -->
        <div v-if="hasCalculationData" class="calculation-section">
          <div class="calc-header">
            <span class="calc-icon">⚙️</span>
            <span class="calc-title">Автоматический расчёт снабжения</span>
          </div>

          <div class="calc-grid">
            <div class="calc-item" v-if="calcData.calculatedAmount">
              <span class="label">Рассчитанный объем:</span>
              <span class="value accent">{{ calcData.calculatedAmount }}</span>
            </div>

            <div class="calc-item" v-if="calcData.recommendedMachinery">
              <span class="label">Рекомендуемая техника:</span>
              <span class="value">{{ calcData.recommendedMachinery }}</span>
            </div>

            <div class="calc-item" v-if="calcData.tripsCount">
              <span class="label">Количество рейсов:</span>
              <span class="value">{{ calcData.tripsCount }}</span>
            </div>

            <div class="calc-item full-width" v-if="calcData.note || calcData.comment">
              <span class="label">Примечание по логистике:</span>
              <span class="value note">{{ calcData.note || calcData.comment }}</span>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatDate, formatUnit } from '../utils/formatters.js'

const props = defineProps({
  item: { type: Object, required: true },
  isPendingDelete: { type: Boolean, default: false }
})

defineEmits(['delete'])

const isExpanded = ref(false)

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

// Форматирование количества с правильным склонением
const displayQuantity = computed(() => {
  const qty = props.item.quantity || props.item.amount || 0
  const unit = props.item.unit || 'шт'
  return formatUnit(qty, unit)
})

// Преобразование даты в формат 07.08.2026
const formattedDate = computed(() => {
  const rawDate = props.item.createdAt || props.item.date
  return formatDate(rawDate)
})

// Корректное отображение спецтехники
const displayUnloading = computed(() => {
  const val = props.item.unloadingEquipment
  if (typeof val === 'boolean') return val ? 'Требуется' : 'Не требуется'
  if (typeof val === 'string') return val === 'Да' || val === 'Требуется' ? 'Требуется' : 'Не требуется'
  return 'Не требуется'
})

const formatPriority = (p) => {
  const map = { CRITICAL: 'Критичный', MEDIUM: 'Средний', LOW: 'Низкий' }
  return map[p] || p
}

// Извлечение посчитанных данных из ответа бэкенда
const calcData = computed(() => {
  const c = props.item.calculation || props.item.calculationResult || props.item
  return {
    calculatedAmount: c.calculatedAmount || c.calculated_amount ? formatUnit(c.calculatedAmount || c.calculated_amount, props.item.unit || 'т') : null,
    recommendedMachinery: c.recommendedMachinery || c.recommended_machinery || c.machinery || null,
    tripsCount: c.tripsCount || c.trips_count || c.trips ? `${c.tripsCount || c.trips_count || c.trips} рейса(ов)` : null,
    note: c.note || c.calcNote || c.logistics_note || null,
    comment: c.comment || null
  }
})

const hasCalculationData = computed(() => {
  return Object.values(calcData.value).some(val => val !== null && val !== undefined)
})
</script>

<style lang="scss" scoped>
.supply-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  transition: box-shadow 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;

  &.pending-delete {
    opacity: 0.5;
    pointer-events: none;
  }

  &:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    cursor: pointer;
    user-select: none;

    .header-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;

      .arrow-icon {
        font-size: 1.25rem;
        font-weight: 800;
        color: #64748b;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-block;
        line-height: 1;

        &.rotated {
          transform: rotate(90deg);
        }
      }

      .title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
      }

      .site-badge {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
      }
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 1rem;

      .quantity {
        font-weight: 700;
        font-size: 0.9rem;
        color: #0f172a;
      }

      .priority-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 6px;

        &.critical { background: #fef2f2; color: #dc2626; }
        &.medium { background: #fefce8; color: #ca8a04; }
        &.low { background: #f0fdf4; color: #16a34a; }
      }

      .btn-delete {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        transition: all 0.2s;

        &:hover {
          color: #ef4444;
          background: #fef2f2;
        }
      }
    }
  }

  .card-details {
    padding: 1rem 1.25rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;

    .details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;

      .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;

        .label {
          font-size: 0.75rem;
          color: #64748b;
          font-weight: 600;
        }
        .value {
          font-size: 0.875rem;
          color: #0f172a;
          font-weight: 600;
        }
      }
    }

    /* СТИЛИ ДЛЯ БЛОКА АВТОРАСЧЕТА */
    .calculation-section {
      margin-top: 1rem;
      padding-top: 0.85rem;
      border-top: 1px dashed #cbd5e1;

      .calc-header {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.6rem;

        .calc-icon {
          font-size: 0.85rem;
        }

        .calc-title {
          font-size: 0.75rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.03em;
          color: #2563eb;
        }
      }

      .calc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.8rem;

        .calc-item {
          display: flex;
          flex-direction: column;
          gap: 0.15rem;

          &.full-width {
            grid-column: 1 / -1;
          }

          .label {
            font-size: 0.725rem;
            color: #64748b;
            font-weight: 600;
          }

          .value {
            font-size: 0.85rem;
            color: #1e293b;
            font-weight: 600;

            &.accent {
              color: #2563eb;
              font-weight: 700;
            }

            &.note {
              font-size: 0.8rem;
              color: #475569;
              font-style: italic;
              font-weight: 400;
            }
          }
        }
      }
    }
  }
}

/* Анимация плавной развертки */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 400px;
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