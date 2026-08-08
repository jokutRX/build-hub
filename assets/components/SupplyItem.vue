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
            <span class="calc-icon" aria-hidden="true">📊</span>
            <span class="calc-title">Автоматический расчёт логистики</span>
            <span class="calc-badge" v-if="calcData.confidence">Высокая точность</span>
          </div>

          <div class="calc-grid">
            <!-- Рассчитанный вес/объём -->
            <div class="calc-card weight-card" v-if="calcData.calculatedAmount">
              <div class="calc-card-icon" aria-hidden="true">⚖️</div>
              <div class="calc-card-content">
                <span class="calc-card-label">Рассчитанный вес</span>
                <span class="calc-card-value accent">{{ calcData.calculatedAmount }}</span>
                <span class="calc-card-hint" v-if="calcData.rawWeight">~{{ calcData.rawWeight }} тонн в расчёте</span>
              </div>
              <button 
                class="calc-card-action" 
                @click.stop="copyToClipboard(calcData.calculatedAmount, 'Вес скопирован')"
                title="Скопировать значение"
                aria-label="Скопировать рассчитанный вес"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
              </button>
            </div>

            <!-- Рекомендуемая техника -->
            <div class="calc-card machinery-card" v-if="calcData.recommendedMachinery">
              <div class="calc-card-icon" aria-hidden="true">{{ machineryIcon }}</div>
              <div class="calc-card-content">
                <span class="calc-card-label">Рекомендуемая техника</span>
                <span class="calc-card-value">{{ calcData.recommendedMachinery }}</span>
                <span class="calc-card-hint" v-if="calcData.machineryCapacity">Грузоподъёмность: {{ calcData.machineryCapacity }}</span>
              </div>
              <button 
                class="calc-card-action" 
                @click.stop="copyToClipboard(calcData.recommendedMachinery, 'Техника скопирована')"
                title="Скопировать название техники"
                aria-label="Скопировать рекомендуемую технику"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
              </button>
            </div>

            <!-- Количество рейсов с визуальным индикатором -->
            <div class="calc-card trips-card" v-if="calcData.tripsCount">
              <div class="calc-card-icon" aria-hidden="true">🔄</div>
              <div class="calc-card-content">
                <span class="calc-card-label">Количество рейсов</span>
                <div class="trips-value-wrap">
                  <span class="calc-card-value accent">{{ calcData.tripsCount }}</span>
                  <span class="trips-unit">рейс{{ tripsPlural }}</span>
                </div>
                <div class="trips-visual" :style="{ '--trips': tripsNumber }" aria-label="{{ calcData.tripsCount }} рейсов">
                  <span v-for="n in tripsNumber" :key="n" class="trip-dot"></span>
                </div>
              </div>
              <button 
                class="calc-card-action" 
                @click.stop="copyToClipboard(calcData.tripsCount, 'Рейсы скопированы')"
                title="Скопировать количество рейсов"
                aria-label="Скопировать количество рейсов"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
              </button>
            </div>

            <!-- Примечание по логистике -->
            <div class="calc-card note-card full-width" v-if="calcData.note || calcData.comment">
              <div class="calc-card-icon" aria-hidden="true">📝</div>
              <div class="calc-card-content full-width">
                <span class="calc-card-label">Примечание по логистике</span>
                <span class="calc-card-value note">{{ calcData.note || calcData.comment }}</span>
              </div>
              <button 
                class="calc-card-action" 
                @click.stop="copyToClipboard(calcData.note || calcData.comment, 'Примечание скопировано')"
                title="Скопировать примечание"
                aria-label="Скопировать примечание по логистике"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Подробности расчёта (expandable) -->
          <div class="calc-details" v-if="showCalcDetails">
            <div class="calc-details-header" @click="showCalcDetails = !showCalcDetails">
              <span class="details-toggle-label">Подробности расчёта</span>
              <span class="details-toggle-icon" :class="{ rotated: showCalcDetails }">›</span>
            </div>
            <Transition name="calc-details">
              <div v-show="showCalcDetails" class="calc-details-content">
                <div class="detail-row" v-if="calcData.rawWeight">
                  <span class="detail-label">Вес в тоннах (сырой):</span>
                  <span class="detail-value">{{ calcData.rawWeight }}</span>
                </div>
                <div class="detail-row" v-if="calcData.density">
                  <span class="detail-label">Плотность материала:</span>
                  <span class="detail-value">{{ calcData.density }} т/м³</span>
                </div>
                <div class="detail-row" v-if="calcData.volume">
                  <span class="detail-label">Объём:</span>
                  <span class="detail-value">{{ calcData.volume }} м³</span>
                </div>
                <div class="detail-row" v-if="calcData.unit">
                  <span class="detail-label">Единица заказа:</span>
                  <span class="detail-value">{{ calcData.unit }}</span>
                </div>
                <div class="detail-row" v-if="calcData.quantity">
                  <span class="detail-label">Количество заказано:</span>
                  <span class="detail-value">{{ calcData.quantity }} {{ calcData.unit }}</span>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatDate, formatUnit, pluralize } from '../utils/formatters.js'

const props = defineProps({
  item: { type: Object, required: true },
  isPendingDelete: { type: Boolean, default: false }
})

defineEmits(['delete'])

const isExpanded = ref(false)
const showCalcDetails = ref(false)

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
  const rawTrips = c.tripsCount || c.trips_count || c.trips
  const rawWeight = c.calculatedAmount || c.calculated_amount
  const rawVolume = c.volume || c.volume_m3
  const density = c.density || c.material_density
  const machinery = c.recommendedMachinery || c.recommended_machinery || c.machinery
  const machineryCap = c.machineryCapacity || c.capacity || c.payload
  
  return {
    calculatedAmount: rawWeight ? formatUnit(rawWeight, 'т') : null,
    rawWeight: rawWeight ? Number(rawWeight).toFixed(2) : null,
    recommendedMachinery: machinery || null,
    machineryCapacity: machineryCap ? `${machineryCap} т` : null,
    tripsCount: rawTrips ? `${rawTrips} рейс${pluralize(rawTrips, ['', 'а', 'ов'])}` : null,
    tripsNumber: rawTrips ? Number(rawTrips) : 0,
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

// Иконка для техники
const machineryIcon = computed(() => {
  const m = (calcData.value.recommendedMachinery || '').toLowerCase()
  if (m.includes('тяжел') || m.includes('тонар') || m.includes('25т')) return '🚛'
  if (m.includes('камаз') || m.includes('20т') || m.includes('самосвал')) return '🚚'
  if (m.includes('маз') || m.includes('10т') || m.includes('самосвал')) return '🚛'
  if (m.includes('газель') || m.includes('мал') || m.includes('3.5')) return '🚐'
  return '🚛'
})

// Склонение слова "рейс"
const tripsPlural = computed(() => {
  const n = calcData.value.tripsNumber
  if (n % 10 === 1 && n % 100 !== 11) return ''
  if ([2, 3, 4].includes(n % 10) && ![12, 13, 14].includes(n % 100)) return 'а'
  return 'ов'
})

const tripsNumber = computed(() => calcData.value.tripsNumber)

// Копирование в буфер обмена с тостом
const copyToClipboard = async (text, successMessage) => {
  try {
    await navigator.clipboard.writeText(text)
    // Показываем временный тултип/уведомление
    showCopyToast(successMessage)
  } catch (err) {
    console.error('Copy failed:', err)
    showCopyToast('Не удалось скопировать', true)
  }
}

const showCopyToast = (message, isError = false) => {
  // Создаем временный элемент для уведомления
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
    animation: slideIn 0.2s ease;
  `
  document.body.appendChild(toast)
  setTimeout(() => {
    toast.style.animation = 'slideOut 0.2s ease'
    setTimeout(() => toast.remove(), 200)
  }, 2000)
}
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
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;

        .calc-icon {
          font-size: 1rem;
        }

        .calc-title {
          font-size: 0.75rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.03em;
          color: #2563eb;
        }

        .calc-badge {
          margin-left: auto;
          font-size: 0.65rem;
          font-weight: 700;
          background: #dcfce7;
          color: #166534;
          padding: 0.15rem 0.5rem;
          border-radius: 9999px;
        }
      }

      .calc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.75rem;
      }

      /* Карточки расчётов */
      .calc-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        transition: all 0.2s ease;
        position: relative;

        &:hover {
          border-color: #cbd5e1;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
          transform: translateY(-1px);
        }

        &.full-width {
          grid-column: 1 / -1;
        }

        .calc-card-icon {
          font-size: 1.25rem;
          flex-shrink: 0;
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f1f5f9;
          border-radius: 8px;
        }

        .calc-card-content {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
          gap: 0.25rem;

          .calc-card-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.02em;
          }

          .calc-card-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;

            &.accent {
              color: #2563eb;
            }

            &.note {
              font-size: 0.85rem;
              font-weight: 400;
              color: #475569;
              font-style: italic;
              line-height: 1.4;
            }
          }

          .calc-card-hint {
            font-size: 0.7rem;
            color: #94a3b8;
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
          border-radius: 6px;
          color: #94a3b8;
          cursor: pointer;
          opacity: 0;
          transition: all 0.2s ease;
          flex-shrink: 0;

          &:hover {
            background: #f1f5f9;
            color: #2563eb;
          }

          svg {
            stroke: currentColor;
          }
        }

        &:hover .calc-card-action {
          opacity: 1;
        }

        /* Варианты карточек */
        &.weight-card {
          border-left: 3px solid #2563eb;
        }

        &.machinery-card {
          border-left: 3px solid #10b981;
        }

        &.trips-card {
          border-left: 3px solid #f59e0b;
        }

        &.note-card {
          border-left: 3px solid #8b5cf6;
        }
      }

      /* Визуальный индикатор рейсов */
      .trips-value-wrap {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
      }

      .trips-unit {
        font-size: 0.8rem;
        font-weight: 500;
        color: #64748b;
      }

      .trips-visual {
        display: flex;
        gap: 3px;
        margin-top: 0.35rem;
        padding-top: 0.35rem;
        border-top: 1px dashed #e2e8f0;

        .trip-dot {
          width: 8px;
          height: 8px;
          background: #e2e8f0;
          border-radius: 50%;
          transition: all 0.2s ease;
        }

        .trip-dot:nth-child(-n + 3) {
          background: #f59e0b;
        }

        .trip-dot:nth-child(n + 4):nth-child(-n + 6) {
          background: #2563eb;
        }

        .trip-dot:nth-child(n + 7) {
          background: #10b981;
        }
      }
    }

    /* Подробности расчёта (expandable) */
    .calc-details {
      margin-top: 1rem;
      padding-top: 0.75rem;
      border-top: 1px dashed #cbd5e1;
      animation: calc-details-slide 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .calc-details-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem 0.75rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;

      &:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
      }

      .details-toggle-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #2563eb;
      }

      .details-toggle-icon {
        font-size: 1rem;
        font-weight: 700;
        color: #64748b;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-block;

        &.rotated {
          transform: rotate(90deg);
        }
      }
    }

    .calc-details-content {
      margin-top: 0.5rem;
      padding: 0.5rem 0.75rem;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      border-top: none;

      .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
        border-bottom: 1px solid #f1f5f9;

        &:last-child {
          border-bottom: none;
          padding-bottom: 0;
        }

        .detail-label {
          font-size: 0.75rem;
          color: #64748b;
          font-weight: 500;
        }

        .detail-value {
          font-size: 0.8rem;
          font-weight: 600;
          color: #0f172a;
          font-family: 'JetBrains Mono', 'Fira Code', monospace;
        }
      }
    }
  }
}

/* Анимации */
@keyframes calc-details-slide {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(10px);
  }
}

/* Анимация плавной развертки */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  max-height: 800px;
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

.calc-details-enter-active,
.calc-details-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  overflow: hidden;
}

.calc-details-enter-from,
.calc-details-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
  overflow: hidden;
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