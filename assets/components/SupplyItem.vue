<template>
  <div :class="['supply-card', { expanded: isExpanded, 'pending-delete': isPendingDelete, 'has-trip': item.tripId }]">
    <!-- Шапка карточки (Кликабельная) -->
    <div class="card-header" @click="toggleExpand">
      <div class="header-left">
        <input 
          type="checkbox" 
          class="row-checkbox" 
          :checked="selected" 
          @click.stop="$emit('select', $event)" 
        />
        <span :class="['arrow-icon', { rotated: isExpanded }]">›</span>
        <h3 class="title">{{ item.title }}</h3>
        <span class="site-badge">{{ item.site || item.object }}</span>
        
        <!-- Плашка номера рейса -->
        <span v-if="item.tripId" class="trip-badge" title="Сгруппировано в рейс">
          ⚡ Рейс #{{ item.tripId }}
        </span>
      </div>

      <div class="header-right">
        <!-- Статус заявки -->
        <span :class="['status-badge', statusInfo.class]">
          {{ statusInfo.text }}
        </span>

        <!-- Склонение количества и единиц -->
        <span class="quantity">{{ displayQuantity }}</span>
        <span :class="['priority-badge', item.priority?.toLowerCase()]">
          {{ formatPriority(item.priority) }}
        </span>
        <button class="btn-duplicate" title="Дублировать" @click.stop="handleDuplicate">
          <Copy :size="16" />
        </button>
        <button class="btn-delete" title="Удалить" @click.stop="$emit('delete', item)">
          <Trash2 :size="16" />
        </button>
      </div>
    </div>

    <!-- Выпадающая часть (Плавный Accordion) -->
    <Transition 
      name="expand"
      @before-enter="onBeforeEnter"
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @before-leave="onBeforeLeave"
      @leave="onLeave"
      @after-leave="onAfterLeave"
    >
      <div v-if="isExpanded" class="card-details">
        <div class="card-details-inner">
          <div class="details-grid">
            <div class="detail-item" v-if="item.status">
              <span class="label">Текущий статус:</span>
              <span class="value">{{ statusInfo.text }}</span>
            </div>

            <div class="detail-item" v-if="item.tripId">
              <span class="label">Привязка к рейсу:</span>
              <span class="value">Рейс № {{ item.tripId }}</span>
            </div>

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

            <div class="detail-item" v-if="item.createdAt || item.date">
              <span class="label">Дата:</span>
              <span class="value">{{ formattedDate }}</span>
            </div>
          </div>

          <div v-if="hasCalculationData" class="calculation-section">
            <div class="calc-header">
              <svg class="calc-icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                <line x1="8" y1="6" x2="16" y2="6"></line>
                <line x1="16" y1="14" x2="16" y2="18"></line>
                <path d="M16 10h.01"></path>
                <path d="M12 10h.01"></path>
                <path d="M8 10h.01"></path>
                <path d="M12 14h.01"></path>
                <path d="M8 14h.01"></path>
                <path d="M12 18h.01"></path>
                <path d="M8 18h.01"></path>
              </svg>
              <span class="calc-title">Автоматический расчёт логистики</span>
              <span class="calc-badge" v-if="calcData.confidence">Высокая точность</span>
            </div>

            <div class="calc-grid">
              <div class="calc-card weight-card" v-if="calcData.calculatedAmount">
                <div class="calc-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18M3 7l9-4 9 4M3 7l3 9a3 3 0 0 0 6 0L9 7M15 7l3 9a3 3 0 0 0 6 0l-3-9"></path>
                  </svg>
                </div>
                <div class="calc-card-content">
                  <span class="calc-card-label">Рассчитанный вес</span>
                  <span class="calc-card-value accent">{{ calcData.calculatedAmount }}</span>
                  <span class="calc-card-hint" v-if="calcData.rawWeight">~{{ calcData.rawWeight }} тонн в расчёте</span>
                </div>
                <button 
                  class="calc-card-action" 
                  @click.stop="copyToClipboard(calcData.calculatedAmount, 'Вес скопирован')"
                  title="Скопировать значение"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                </button>
              </div>

              <div class="calc-card machinery-card" v-if="calcData.recommendedMachinery">
                <div class="calc-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13" rx="2"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                  </svg>
                </div>
                <div class="calc-card-content">
                  <span class="calc-card-label">Рекомендуемая техника</span>
                  <span class="calc-card-value">{{ calcData.recommendedMachinery }}</span>
                  <span class="calc-card-hint" v-if="calcData.machineryCapacity">Грузоподъёмность: {{ calcData.machineryCapacity }}</span>
                </div>
                <button 
                  class="calc-card-action" 
                  @click.stop="copyToClipboard(calcData.recommendedMachinery, 'Техника скопирована')"
                  title="Скопировать название техники"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                </button>
              </div>

              <div class="calc-card trips-card" v-if="calcData.tripsCount">
                <div class="calc-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="17 1 21 5 17 9"></polyline>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                    <polyline points="7 23 3 19 7 15"></polyline>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                  </svg>
                </div>
                <div class="calc-card-content">
                  <span class="calc-card-label">Логистика</span>
                  <div class="trips-value-wrap">
                    <span class="calc-card-value accent">{{ calcData.tripsCount }}</span>
                    <span class="trips-unit">/ {{ calcData.estimatedCost }} ₽</span>
                  </div>
                  <div class="trips-visual" :style="{ '--trips': tripsNumber }">
                    <span v-for="n in tripsNumber" :key="n" class="trip-dot"></span>
                  </div>
                </div>
                <button 
                  class="calc-card-action" 
                  @click.stop="copyToClipboard(calcData.tripsCount + ' / ' + calcData.estimatedCost + ' руб', 'Данные скопированы')"
                  title="Скопировать расчёт"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatDate, formatUnit, pluralize } from '../utils/formatters.js'
import { Copy, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  item: { type: Object, required: true },
  isPendingDelete: { type: Boolean, default: false },
  selected: { type: Boolean, default: false }
})

const emit = defineEmits(['delete', 'duplicate', 'select'])

const isExpanded = ref(false)

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

const handleDuplicate = () => {
  emit('duplicate', props.item)
}

// Маппинг статусов
const statusInfo = computed(() => {
  const status = props.item.status || 'NEW'
  const map = {
    NEW: { text: 'Новая', class: 'status-new' },
    IN_TRIP: { text: 'В рейсе', class: 'status-trip' },
    IN_TRANSIT: { text: 'В пути', class: 'status-transit' },
    COMPLETED: { text: 'Завершено', class: 'status-completed' },
    CANCELLED: { text: 'Отменено', class: 'status-cancelled' }
  }
  return map[status] || { text: status, class: 'status-default' }
})

// JS-хуки для анимации accordion
const onBeforeEnter = (el) => {
  el.style.height = '0'
  el.style.opacity = '0'
  el.style.overflow = 'hidden'
}

const onEnter = (el) => {
  el.style.height = el.scrollHeight + 'px'
  el.style.opacity = '1'
}

const onAfterEnter = (el) => {
  el.style.height = ''
  el.style.opacity = ''
  el.style.overflow = ''
}

const onBeforeLeave = (el) => {
  el.style.height = el.scrollHeight + 'px'
  el.style.opacity = '1'
  el.style.overflow = 'hidden'
}

const onLeave = (el) => {
  void el.offsetHeight
  el.style.height = '0'
  el.style.opacity = '0'
}

const onAfterLeave = (el) => {
  el.style.height = ''
  el.style.opacity = ''
  el.style.overflow = ''
}

const copyToClipboard = (text, message) => {
  if (!text) return
  navigator.clipboard.writeText(text).then(() => {
    showCopyToast(message)
  }).catch(() => {
    showCopyToast('Не удалось скопировать', true)
  })
}

const displayQuantity = computed(() => {
  const qty = props.item.quantity || props.item.amount || 0
  const unit = props.item.unit || 'шт'
  return formatUnit(qty, unit)
})

const formattedDate = computed(() => {
  const rawDate = props.item.createdAt || props.item.date
  return formatDate(rawDate)
})

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

const calcData = computed(() => {
  const c = props.item.calculation || props.item.calculationResult || props.item
  const rawTrips = c.tripsCount || c.trips_count || c.trips || 0
  const rawWeight = c.calculatedAmount || c.calculated_amount
  const rawVolume = c.volume || c.volume_m3
  const density = c.density || c.material_density
  const machinery = c.recommendedMachinery || c.recommended_machinery || c.machinery
  const machineryCap = c.machineryCapacity || c.capacity || c.payload
  
  const isHeavy = machineryCap && parseFloat(machineryCap) > 15
  const pricePerTrip = isHeavy ? 7000 : 3000
  const totalCost = rawTrips * pricePerTrip
  
  return {
    calculatedAmount: rawWeight ? formatUnit(rawWeight, 'т') : null,
    rawWeight: rawWeight ? Number(rawWeight).toFixed(2) : null,
    recommendedMachinery: machinery || null,
    machineryCapacity: machineryCap ? `${machineryCap} т` : null,
    tripsCount: rawTrips ? `${rawTrips} рейс${pluralize(rawTrips, ['', 'а', 'ов'])}` : null,
    tripsNumber: Number(rawTrips),
    estimatedCost: totalCost.toLocaleString('ru-RU'),
    note: c.note || c.calcNote || c.logistics_note || null,
    comment: c.comment || null,
    density: density ? Number(density).toFixed(2) : null,
    volume: rawVolume ? Number(rawVolume).toFixed(2) : null,
    unit: props.item.unit || null,
    quantity: props.item.quantity || props.item.amount || null,
    confidence: c.confidence || (rawWeight && machinery ? true : false)
  }
})

const hasCalculationData = computed(() => {
  return Object.values(calcData.value).some(val => val !== null && val !== undefined && val !== '')
})

const tripsNumber = computed(() => calcData.value.tripsNumber)

const showCopyToast = (message, isError = false) => {
  const toast = document.createElement('div')
  toast.className = `copy-toast ${isError ? 'error' : ''}`
  toast.textContent = message
  toast.style.cssText = `
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    background: ${isError ? '#ef4444' : '#10b981'};
    color: white;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    transition: opacity 0.2s ease;
  `
  document.body.appendChild(toast)
  setTimeout(() => {
    toast.style.opacity = '0'
    setTimeout(() => toast.remove(), 200)
  }, 2000)
}
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.supply-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  margin-bottom: var(--space-3);
  overflow: hidden;
  transition: box-shadow var(--transition-base), border-color var(--transition-base), opacity var(--transition-base);

  &.has-trip {
    border-left: 3px solid var(--color-primary);
  }

  &.pending-delete {
    opacity: 0.5;
    pointer-events: none;
    transition: opacity 0.3s ease;
  }

  &:hover {
    border-color: var(--color-border-strong);
    box-shadow: var(--shadow-md);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) var(--space-5);
    cursor: pointer;
    user-select: none;

    .header-left {
      display: flex;
      align-items: center;
      gap: var(--space-3);
      flex-wrap: wrap;

      .row-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: var(--color-primary);
        margin: 0;
      }

      .arrow-icon {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--color-text-muted);
        transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        display: inline-block;
        line-height: 1;

        &.rotated {
          transform: rotate(90deg);
        }
      }

      .title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--color-text-main);
        margin: 0;
      }

      .site-badge {
        background: var(--color-bg-secondary);
        color: var(--color-text-secondary);
        font-size: 0.75rem;
        font-weight: 600;
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius-sm);
      }

      .trip-badge {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(99, 102, 241, 0.25);
      }
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: var(--space-4);

      .status-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: var(--radius-sm);

        &.status-new {
          background: rgba(59, 130, 246, 0.12);
          color: #2563eb;
        }

        &.status-trip {
          background: rgba(99, 102, 241, 0.12);
          color: #4f46e5;
        }

        &.status-transit {
          background: rgba(245, 158, 11, 0.12);
          color: #d97706;
        }

        &.status-completed {
          background: rgba(16, 185, 129, 0.12);
          color: #059669;
        }

        &.status-cancelled {
          background: rgba(239, 68, 68, 0.12);
          color: #dc2626;
        }

        &.status-default {
          background: var(--color-bg-secondary);
          color: var(--color-text-muted);
        }
      }

      .quantity {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--color-text-main);
      }

      .priority-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius-sm);

        &.critical { background: var(--color-critical-bg); color: var(--color-critical); }
        &.medium { background: var(--color-medium-bg); color: var(--color-medium); }
        &.low { background: var(--color-low-bg); color: var(--color-low); }
      }

      .btn-delete, .btn-duplicate {
        background: transparent;
        border: none;
        color: var(--color-text-light);
        cursor: pointer;
        padding: var(--space-1) var(--space-2);
        border-radius: var(--radius-xs);
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;

        &:hover {
          background: var(--color-bg-secondary);
        }
      }

      .btn-delete:hover {
        color: var(--color-critical);
        background: var(--color-critical-bg);
      }

      .btn-duplicate:hover {
        color: var(--color-primary);
      }
    }
  }

  .card-details {
    background: var(--color-bg-main);
    border-top: 1px solid var(--color-border);

    .card-details-inner {
      padding: var(--space-4) var(--space-5);
    }

    .details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: var(--space-4);

      .detail-item {
        display: flex;
        flex-direction: column;
        gap: var(--space-1);

        .label {
          font-size: 0.75rem;
          color: var(--color-text-muted);
          font-weight: 600;
        }
        .value {
          font-size: 0.875rem;
          color: var(--color-text-main);
          font-weight: 600;
        }
      }
    }

    .calculation-section {
      margin-top: var(--space-4);
      padding-top: var(--space-3);
      border-top: 1px dashed var(--color-border);

      .calc-header {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        margin-bottom: var(--space-3);
        flex-wrap: wrap;

        .calc-icon-svg {
          color: var(--color-primary);
          flex-shrink: 0;
        }

        .calc-title {
          font-size: 0.75rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.03em;
          color: var(--color-primary);
        }

        .calc-badge {
          margin-left: auto;
          font-size: 0.65rem;
          font-weight: 600;
          background: var(--color-bg-secondary);
          color: var(--color-text-muted);
          border: 1px solid var(--color-border);
          padding: 2px 8px;
          border-radius: var(--radius-sm);
        }
      }

      .calc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--space-3);
      }

      .calc-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        transition: all var(--transition-base);
        position: relative;

        &:hover {
          border-color: var(--color-border-strong);
          box-shadow: var(--shadow-md);
          transform: translateY(-1px);
        }

        .calc-card-icon {
          color: var(--color-text-secondary);
          flex-shrink: 0;
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: var(--color-bg-secondary);
          border-radius: var(--radius-md);
          border: 1px solid var(--color-border);
        }

        .calc-card-content {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
          gap: var(--space-1);

          .calc-card-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.02em;
          }

          .calc-card-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-text-main);
            word-break: break-word;

            &.accent {
              color: var(--color-primary);
            }
          }

          .calc-card-hint {
            font-size: 0.7rem;
            color: var(--color-text-light);
            font-weight: 500;
          }
        }

        .calc-card-action {
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: transparent;
          border: none;
          border-radius: var(--radius-sm);
          color: var(--color-text-light);
          cursor: pointer;
          opacity: 0;
          transition: all var(--transition-fast);
          flex-shrink: 0;

          &:hover {
            background: var(--color-bg-secondary);
            color: var(--color-primary);
          }

          svg {
            stroke: currentColor;
          }
        }

        &:hover .calc-card-action {
          opacity: 1;
        }
      }

      .trips-value-wrap {
        display: flex;
        align-items: baseline;
        gap: var(--space-1);
      }

      .trips-unit {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--color-text-muted);
      }

      .trips-visual {
        display: flex;
        gap: 3px;
        margin-top: var(--space-1);
        padding-top: var(--space-1);
        border-top: 1px dashed var(--color-border);

        .trip-dot {
          width: 8px;
          height: 8px;
          background: var(--color-border);
          border-radius: 50%;
          transition: all var(--transition-fast);
        }

        .trip-dot:nth-child(-n + 3) {
          background: var(--color-warning);
        }

        .trip-dot:nth-child(n + 4):nth-child(-n + 6) {
          background: var(--color-primary);
        }

        .trip-dot:nth-child(n + 7) {
          background: var(--color-success);
        }
      }
    }
  }
}

.expand-enter-active,
.expand-leave-active {
  transition: height 0.3s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.25s cubic-bezier(0.25, 1, 0.5, 1);
  will-change: height, opacity;
}
</style>