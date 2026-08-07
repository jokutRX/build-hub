<template>
  <div class="form-container">
    <!-- Шапка панели с кнопкой закрытия -->
    <div class="form-header">
      <div class="title-group">
        <h3>Новая заявка на закупку</h3>
        <p>Укажите детали позиции и приоритет поставки</p>
      </div>
      <button class="btn-close" @click="$emit('close')" title="Закрыть">✕</button>
    </div>

    <!-- Тело формы -->
    <form @submit.prevent="handleSubmit" class="form-body">
      <div class="form-fields-wrapper">
        <!-- Наименование -->
        <div class="form-field">
          <label>НАИМЕНОВАНИЕ МАТЕРИАЛА / ОБОРУДОВАНИЯ</label>
          <input 
            v-model="form.title" 
            type="text" 
            placeholder="Например: Арматура А500С 12мм" 
            required 
          />
        </div>

        <!-- Объект -->
        <div class="form-field">
          <label>ОБЪЕКТ / ПЛОЩАДКА</label>
          <select v-model="form.object">
            <option value="ЖК Северный">ЖК Северный</option>
            <option value="ЖК Южный">ЖК Южный</option>
            <option value="ТЦ Центральный">ТЦ Центральный</option>
          </select>
        </div>

        <!-- Количество и Ед. измерения -->
        <div class="form-row">
          <div class="form-field">
            <label>КОЛИЧЕСТВО</label>
            <input 
              v-model.number="form.amount" 
              type="number" 
              step="0.1" 
              class="input-no-spinner"
              required 
            />
          </div>
          <div class="form-field">
            <label>ЕД. ИЗМЕРЕНИЯ</label>
            <select v-model="form.unit">
              <option value="тонны">тонны</option>
              <option value="шт">шт</option>
              <option value="м²">м²</option>
              <option value="м³">м³</option>
            </select>
          </div>
        </div>

        <!-- Приоритет -->
        <div class="form-field">
          <label>ПРИОРИТЕТ СНАБЖЕНИЯ</label>
          <div class="priority-selector">
            <button 
              type="button"
              :class="['priority-btn', 'low', { active: form.priority === 'LOW' }]"
              @click="form.priority = 'LOW'"
            >
              ● НИЗКИЙ
            </button>
            <button 
              type="button"
              :class="['priority-btn', 'medium', { active: form.priority === 'MEDIUM' }]"
              @click="form.priority = 'MEDIUM'"
            >
              ● СРЕДНИЙ
            </button>
            <button 
              type="button"
              :class="['priority-btn', 'critical', { active: form.priority === 'CRITICAL' }]"
              @click="form.priority = 'CRITICAL'"
            >
              ● КРИТИЧНЫЙ
            </button>
          </div>
        </div>
      </div>

      <!-- Кнопки действий всегда прижаты к низу -->
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="$emit('close')">
          Отмена
        </button>
        <button type="submit" class="btn-submit">
          + Добавить в реестр
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['create', 'close'])

const form = reactive({
  title: '',
  object: 'ЖК Северный',
  amount: 100,
  unit: 'тонны',
  priority: 'MEDIUM'
})

const handleSubmit = () => {
  emit('create', { ...form })
  form.title = ''
  form.amount = 100
  form.priority = 'MEDIUM'
}
</script>

<style lang="scss" scoped>
@use "sass:color";
@use "../styles/main.scss" as *;

.form-container {
  display: flex;
  flex-direction: column;
  height: 100%;

  .form-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid $border;
    flex-shrink: 0;

    .title-group {
      h3 { font-size: 1.2rem; font-weight: 800; color: $text-main; margin: 0; }
      p { font-size: 0.85rem; color: $text-muted; margin: 0.25rem 0 0 0; }
    }

    .btn-close {
      background: transparent;
      border: none;
      font-size: 1.25rem;
      color: $text-muted;
      cursor: pointer;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      &:hover { background: #f1f5f9; color: $text-main; }
    }
  }

  .form-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    padding-top: 1.5rem;
    overflow: hidden; /* Ограничиваем контент для красивого прижатия кнопок */

    .form-fields-wrapper {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
      overflow-y: auto; /* Внутренний скролл только для полей, если экран очень маленький */
      padding-right: 0.25rem;
    }

    .form-field {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;

      label {
        font-size: 0.7rem;
        font-weight: 800;
        color: $text-muted;
        letter-spacing: 0.05em;
      }

      input, select {
        padding: 0.65rem 0.85rem;
        border: 1px solid $border;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        &:focus { border-color: $primary; }
      }

      /* Скрытие стрелочек спиннера у инпута типа number */
      .input-no-spinner {
        -moz-appearance: textfield;
        &::-webkit-outer-spin-button,
        &::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }
      }
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .priority-selector {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;

      .priority-btn {
        padding: 0.6rem 0.4rem;
        border: 1px solid $border;
        background: #fff;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;

        &.low {
          color: #16a34a;
          &.active { background: #f0fdf4; border-color: #22c55e; }
        }
        &.medium {
          color: #d97706;
          &.active { background: #fefce8; border-color: #eab308; }
        }
        &.critical {
          color: #dc2626;
          &.active { background: #fef2f2; border-color: #ef4444; }
        }
      }
    }

    /* Фиксированная футер-зона с кнопками */
    .form-actions {
      padding-top: 1.25rem;
      margin-top: 1.25rem;
      border-top: 1px solid $border;
      display: flex;
      gap: 0.75rem;
      flex-shrink: 0;

      .btn-cancel {
        flex: 1;
        padding: 0.75rem;
        background: #f1f5f9;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        color: $text-muted;
        cursor: pointer;
        &:hover { background: #e2e8f0; }
      }

      .btn-submit {
        flex: 2;
        padding: 0.75rem;
        background: $primary;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        &:hover { background: color.adjust(#2563eb, $lightness: -5%); }
      }
    }
  }
}
</style>