<template>
  <div class="form-container">
    <!-- Шапка панели с кнопкой закрытия -->
    <div class="form-header">
      <div class="title-group">
        <h3>Новая заявка на закупку</h3>
        <p>Укажите детали позиции и приоритет поставки</p>
      </div>
      <button 
        class="btn-close" 
        :disabled="isSubmitting" 
        @click="$emit('close')" 
        title="Закрыть"
      >
        ✕
      </button>
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
            :disabled="isSubmitting"
            required 
          />
        </div>

        <!-- Объект -->
        <div class="form-field">
          <label>ОБЪЕКТ / ПЛОЩАДКА</label>
          <select v-model="form.object" :disabled="isSubmitting">
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
              :disabled="isSubmitting"
              required 
            />
          </div>
          <div class="form-field">
            <label>ЕД. ИЗМЕРЕНИЯ</label>
            <select v-model="form.unit" :disabled="isSubmitting">
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
              :disabled="isSubmitting"
              @click="form.priority = 'LOW'"
            >
              ● НИЗКИЙ
            </button>
            <button 
              type="button"
              :class="['priority-btn', 'medium', { active: form.priority === 'MEDIUM' }]"
              :disabled="isSubmitting"
              @click="form.priority = 'MEDIUM'"
            >
              ● СРЕДНИЙ
            </button>
            <button 
              type="button"
              :class="['priority-btn', 'critical', { active: form.priority === 'CRITICAL' }]"
              :disabled="isSubmitting"
              @click="form.priority = 'CRITICAL'"
            >
              ● КРИТИЧНЫЙ
            </button>
          </div>
        </div>
      </div>

      <!-- Кнопки действий -->
      <div class="form-actions">
        <button 
          type="button" 
          class="btn-cancel" 
          :disabled="isSubmitting" 
          @click="$emit('close')"
        >
          Отмена
        </button>

        <button 
          type="submit" 
          class="btn-submit" 
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting" class="btn-loader"></span>
          <span>{{ isSubmitting ? 'Сохранение...' : '+ Добавить в реестр' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['create', 'close'])

const isSubmitting = ref(false)

const form = reactive({
  title: '',
  object: 'ЖК Северный',
  amount: 100,
  unit: 'тонны',
  priority: 'MEDIUM'
})

const handleSubmit = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true

  try {
    // Отправляем событие наружу (поддерживает как обычный вызов, так и асинхронный Promise)
    await emit('create', { ...form })
    
    // Сбрасываем форму только после успешной отправки
    form.title = ''
    form.amount = 100
    form.priority = 'MEDIUM'
  } catch (err) {
    console.error('Ошибка при отправке формы:', err)
  } finally {
    isSubmitting.value = false
  }
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

      &:hover:not(:disabled) { background: #f1f5f9; color: $text-main; }
      &:disabled { opacity: 0.5; cursor: not-allowed; }
    }
  }

  .form-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    padding-top: 1.5rem;
    overflow: hidden;

    .form-fields-wrapper {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
      overflow-y: auto;
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
        transition: border-color 0.2s, background-color 0.2s;

        &:focus:not(:disabled) { border-color: $primary; }
        &:disabled {
          background-color: #f8fafc;
          color: $text-muted;
          cursor: not-allowed;
        }
      }

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

        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      }
    }

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
        transition: background 0.2s;

        &:hover:not(:disabled) { background: #e2e8f0; }
        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
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
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: background 0.2s, opacity 0.2s;

        &:hover:not(:disabled) { 
          background: color.adjust(#2563eb, $lightness: -5%); 
        }

        &:disabled {
          opacity: 0.65;
          cursor: not-allowed;
          background: $primary;
        }

        /* Микро-спиннер во время загрузки */
        .btn-loader {
          width: 14px;
          height: 14px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          border-top-color: #ffffff;
          border-radius: 50%;
          animation: spin 0.6s linear infinite;
        }
      }
    }
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>