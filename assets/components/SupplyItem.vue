<script setup>
import { computed } from 'vue'

const props = defineProps({
  request: { type: Object, required: true }
})

const emit = defineEmits(['delete'])

// 🔍 Печатаем пришедший объект от Symfony в консоль браузера
console.log('Данные заявки из Symfony:', props.request)

// 1. Универсальный поиск ID (проверяет все возможные ключи из Symfony/Doctrine)
const requestId = computed(() => {
  const r = props.request
  if (!r) return undefined
  return r.id ?? r._id ?? r.requestId ?? r.request_id ?? r.$id
})

// 2. Универсальное извлечение остальных полей
const displayTitle = computed(() => {
  const r = props.request
  return r.title || r.name || r.description || r.material || 'Без названия'
})

const displaySite = computed(() => {
  const r = props.request
  return r.site || r.siteName || r.site_name || r.location || 'Не указан'
})

const displayQuantity = computed(() => {
  const r = props.request
  return r.quantity ?? r.amount ?? r.count ?? 0
})

const displayUnit = computed(() => {
  const r = props.request
  return r.unit || r.unitName || r.unit_name || 'шт'
})

const priorityConfig = computed(() => {
  const priority = props.request.priority || props.request.priorityLevel
  switch (priority) {
    case 'critical': return { label: 'Критичный', class: 'critical' }
    case 'medium': return { label: 'Средний', class: 'medium' }
    case 'low': return { label: 'Низкий', class: 'low' }
    default: return { label: 'Обычный', class: 'low' }
  }
})

const handleDelete = () => {
  if (requestId.value !== undefined && requestId.value !== null) {
    emit('delete', requestId.value)
  } else {
    console.error('❌ Ошибка: не удалось определить ID у объекта:', props.request)
  }
}
</script>

<template>
  <div class="request-card" :class="priorityConfig.class">
    <div class="card-left">
      <div class="badges">
        <span class="badge-priority" :class="priorityConfig.class">
          {{ priorityConfig.label }}
        </span>
        <span class="site-badge">📍 {{ displaySite }}</span>
      </div>
      
      <h4 class="title">{{ displayTitle }}</h4>
    </div>

    <div class="card-right">
      <div class="volume">
        <span class="value">{{ displayQuantity }}</span>
        <span class="unit">{{ displayUnit }}</span>
      </div>

      <button @click="handleDelete" class="btn-delete" title="Удалить заявку">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "sass:color";
@use "../styles/main.scss" as *;

.request-card {
  background: $surface;
  border: 1px solid $border;
  border-left: 4px solid $border;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  @include card-shadow;

  &.critical { border-left-color: $critical; }
  &.medium { border-left-color: $medium; }
  &.low { border-left-color: $low; }

  .card-left {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;

    .badges {
      display: flex;
      align-items: center;
      gap: 0.6rem;

      .badge-priority {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        letter-spacing: 0.04em;

        &.critical { background: $critical-bg; color: $critical; border: 1px solid $critical-border; }
        &.medium { background: $medium-bg; color: color.adjust($medium, $lightness: -10%); border: 1px solid $medium-border; }
        &.low { background: $low-bg; color: color.adjust($low, $lightness: -10%); border: 1px solid $low-border; }
      }

      .site-badge { font-size: 0.8rem; color: $text-muted; font-weight: 500; }
    }

    .title { margin: 0; font-size: 1.05rem; font-weight: 600; color: $text-main; }
  }

  .card-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;

    .volume {
      text-align: right;

      .value { font-size: 1.25rem; font-weight: 700; color: $text-main; }
      .unit { font-size: 0.85rem; color: $text-muted; margin-left: 0.3rem; }
    }

    .btn-delete {
      background: transparent;
      border: none;
      color: $text-light;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 8px;
      display: flex;
      align-items: center;
      transition: all 0.2s;

      &:hover { background: $critical-bg; color: $critical; }
    }
  }
}
</style>