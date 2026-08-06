<script setup>
import { ref } from 'vue'

const emit = defineEmits(['create'])

const title = ref('')
const site = ref('ЖК Северный')
const quantity = ref(100)
const unit = ref('тонны')
const priority = ref('medium')

const handleSubmit = () => {
  if (!title.value.trim() || !quantity.value) return

  emit('create', {
    title: title.value,
    site: site.value,
    quantity: Number(quantity.value),
    unit: unit.value,
    priority: priority.value
  })

  title.value = ''
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="supply-form">
    <div class="form-header">
      <div class="header-icon">📦</div>
      <div>
        <h3>Создать заявку на закупку</h3>
        <p>Укажите детали позиции и приоритет поставки на объект</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="field col-full">
        <label>Наименование материала / оборудования</label>
        <input 
          v-model="title" 
          type="text" 
          placeholder="Например: Арматура А500С 12мм" 
          required 
        />
      </div>

      <div class="field">
        <label>Объект / Площадка</label>
        <select v-model="site">
          <option value="ЖК Северный">ЖК Северный</option>
          <option value="ЖК Невский">ЖК Невский</option>
          <option value="БЦ Горизонт">БЦ Горизонт</option>
        </select>
      </div>

      <div class="field">
        <label>Количество</label>
        <input 
          v-model="quantity" 
          type="number" 
          step="0.1" 
          min="0.1" 
          required 
        />
      </div>

      <div class="field">
        <label>Ед. измерения</label>
        <select v-model="unit">
          <option value="тонны">тонны</option>
          <option value="шт">шт</option>
          <option value="м3">м³</option>
          <option value="м2">м²</option>
          <option value="п.м.">п.м.</option>
        </select>
      </div>

      <div class="field col-full">
        <label>Приоритет снабжения</label>
        <div class="priority-selector">
          <label class="priority-btn low" :class="{ active: priority === 'low' }">
            <input type="radio" value="low" v-model="priority" />
            <span class="dot"></span> Низкий
          </label>
          <label class="priority-btn medium" :class="{ active: priority === 'medium' }">
            <input type="radio" value="medium" v-model="priority" />
            <span class="dot"></span> Средний
          </label>
          <label class="priority-btn critical" :class="{ active: priority === 'critical' }">
            <input type="radio" value="critical" v-model="priority" />
            <span class="dot"></span> Критичный
          </label>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-primary">
        + Добавить в реестр
      </button>
    </div>
  </form>
</template>

<style lang="scss" scoped>
@use "sass:color";
@use "../styles/main.scss" as *;

.supply-form {
  background: $surface;
  border: 1px solid $border;
  border-radius: 16px;
  padding: 1.75rem;
  margin-bottom: 2.5rem;
  @include card-shadow;

  .form-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;

    .header-icon {
      font-size: 1.5rem;
      background: $primary-light;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
    }

    h3 { margin: 0; font-size: 1.15rem; font-weight: 600; color: $text-main; }
    p { margin: 0.15rem 0 0 0; font-size: 0.85rem; color: $text-muted; }
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;

    .col-full { grid-column: span 3; }

    .field {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;

      label { 
        font-size: 0.75rem; 
        font-weight: 700; 
        color: $text-muted; 
        text-transform: uppercase; 
        letter-spacing: 0.04em; 
      }

      input, select { @include input-base; }
    }
  }

  .priority-selector {
    display: flex;
    gap: 0.75rem;

    .priority-btn {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.65rem;
      border: 1px solid $border;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s;

      input { display: none; }
      .dot { width: 8px; height: 8px; border-radius: 50%; }

      &.low { .dot { background: $low; } }
      &.medium { .dot { background: $medium; } }
      &.critical { .dot { background: $critical; } }

      &.active.low { background: $low-bg; border-color: $low-border; color: color.adjust($low, $lightness: -15%); }
      &.active.medium { background: $medium-bg; border-color: $medium-border; color: color.adjust($medium, $lightness: -15%); }
      &.active.critical { background: $critical-bg; border-color: $critical-border; color: color.adjust($critical, $lightness: -10%); }
    }
  }

  .form-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;

    .btn-primary {
      background: $primary;
      color: white;
      border: none;
      padding: 0.75rem 1.75rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;

      &:hover { background: $primary-hover; }
      &:active { transform: scale(0.98); }
    }
  }
}
</style>