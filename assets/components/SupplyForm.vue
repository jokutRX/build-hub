<template>
  <div class="form-container">
    <div class="form-header">
      <h2>Новая заявка</h2>
      <button class="btn-close" type="button" @click="$emit('close')">✕</button>
    </div>

    <form @submit.prevent="handleSubmit" class="supply-form">
      <!-- Наименование -->
      <div class="form-group">
        <label>Наименование материала / оборудования *</label>
        <input 
          ref="titleInput"
          type="text" 
          v-model.trim="form.title" 
          placeholder="Например: Бетон М300" 
          required 
          class="form-input"
          @keyup.enter="handleSubmit"
        />
      </div>

      <!-- Объект -->
      <div class="form-group">
        <label>Строительный объект *</label>
        <input 
          type="text" 
          v-model.trim="form.object" 
          placeholder="Например: ТЦ Центральный" 
          required 
          class="form-input"
          @keyup.enter="handleSubmit"
        />
      </div>

      <!-- Количество (Без стрелочек) и Единицы измерения -->
      <div class="form-row">
        <div class="form-group">
          <label>Количество *</label>
          <input 
            type="number" 
            v-model.number="form.amount" 
            min="0.1" 
            step="any" 
            required 
            class="form-input no-spinners"
            @keyup.enter="handleSubmit"
          />
        </div>

        <div class="form-group">
          <label>Ед. измерения</label>
          <select v-model="form.unit" class="form-select" @keyup.enter="handleSubmit">
            <option value="тонны">тонны</option>
            <option value="шт">шт</option>
            <option value="м³">м³</option>
            <option value="м²">м²</option>
            <option value="кг">кг</option>
          </select>
        </div>
      </div>

      <!-- Приоритет -->
      <div class="form-group">
        <label>Приоритет</label>
        <select v-model="form.priority" class="form-select" @keyup.enter="handleSubmit">
          <option value="CRITICAL">Критичный</option>
          <option value="MEDIUM">Средний</option>
          <option value="LOW">Низкий</option>
        </select>
      </div>

      <!-- Окно доставки (24-часовой формат, step=60) -->
      <div class="form-row">
        <div class="form-group">
          <label>Время доставки С</label>
          <input 
            type="time" 
            v-model="form.deliveryTimeStart" 
            step="60"
            class="form-input"
            @keyup.enter="handleSubmit"
          />
        </div>

        <div class="form-group">
          <label>Время доставки ДО</label>
          <input 
            type="time" 
            v-model="form.deliveryTimeEnd" 
            step="60"
            :class="['form-input', { 'input-error': !!timeError }]"
            @keyup.enter="handleSubmit"
          />
        </div>
      </div>

      <!-- Вывод ошибки времени и блокировка -->
      <div v-if="timeError" class="error-banner">
        ⚠️ {{ timeError }}
      </div>

      <!-- Разгрузочная техника -->
      <div class="form-group checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.unloadingEquipment" />
          <span>Требуется спецтехника для разгрузки</span>
        </label>
      </div>

      <!-- Кнопки действий -->
      <div class="form-actions">
        <button type="button" class="btn-secondary" @click="$emit('close')">Отмена</button>
        <button type="submit" class="btn-primary" :disabled="isSubmitDisabled">
          Создать заявку
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const emit = defineEmits(['create', 'close'])

const titleInput = ref(null)

onMounted(() => {
  titleInput.value?.focus()
})

const initialForm = {
  title: '',
  object: '',
  amount: 1,
  unit: 'тонны',
  priority: 'MEDIUM',
  deliveryTimeStart: '09:00',
  deliveryTimeEnd: '12:00',
  unloadingEquipment: false
}

const form = reactive({ ...initialForm })

// Функция сброса формы к начальным значениям
const resetForm = () => {
  Object.assign(form, initialForm)
}

// Проверка корректности промежутка времени
const timeError = computed(() => {
  if (!form.deliveryTimeStart || !form.deliveryTimeEnd) return ''

  // Сравнение строк в формате "HH:mm" работает корректно (например "09:00" > "07:00")
  if (form.deliveryTimeEnd < form.deliveryTimeStart) {
    return 'Время окончания не может быть раньше времени начала (разгрузка в пределах одних суток).'
  }

  return ''
})

// Блокировка кнопки если есть ошибка по времени или не заполнены обязательные поля
const isSubmitDisabled = computed(() => {
  return !!timeError.value || !form.title || !form.object || !form.amount
})

const handleSubmit = () => {
  if (isSubmitDisabled.value) return
  // Передаем копию данных формы и колбэк для сброса
  emit('create', { ...form }, resetForm)
}
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.form-container {
  display: flex;
  flex-direction: column;
  height: 100%;

  .form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;

    h2 {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--color-text-main);
      margin: 0;
    }

    .btn-close {
      background: transparent;
      border: none;
      font-size: 1.2rem;
      color: var(--color-text-light);
      cursor: pointer;
      padding: 0.25rem 0.5rem;
      border-radius: var(--radius-sm);

      &:hover {
        background: var(--color-bg-secondary);
        color: var(--color-text-main);
      }
    }
  }

  .supply-form {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;

      label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--color-text-muted);
      }

      .form-input, .form-select {
        @include input-base;

        &.input-error {
          border-color: var(--color-critical);
          background-color: var(--color-critical-bg);
        }
      }
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .error-banner {
      background-color: var(--color-critical-bg);
      color: var(--color-critical);
      border: 1px solid var(--color-critical-border);
      padding: 0.6rem 0.8rem;
      border-radius: var(--radius-md);
      font-size: 0.8rem;
      font-weight: 600;
      line-height: 1.3;
    }

    .checkbox-group {
      margin-top: 0.25rem;

      .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-text-main);

        input[type="checkbox"] {
          width: 16px;
          height: 16px;
          accent-color: var(--color-primary);
          cursor: pointer;
        }
      }
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1.5rem;

      button {
        padding: 0.65rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        border: none;
        transition: all var(--transition-fast);
      }

      .btn-secondary {
        background: var(--color-bg-secondary);
        color: var(--color-text-muted);

        &:hover {
          background: var(--color-bg-tertiary);
        }
      }

      .btn-primary {
        background: var(--color-primary);
        color: var(--color-primary-contrast);

        &:hover:not(:disabled) {
          background: var(--color-primary-hover);
        }

        &:disabled {
          background: var(--color-text-light);
          opacity: 0.6;
          cursor: not-allowed;
        }
      }
    }
  }
}
</style>
