<template>
  <div class="page-container">
    <!-- Шапка страницы -->
    <header class="page-header">
      <div class="header-text">
        <h1>Заявки на закупку</h1>
        <p>Управление потребностями объектов и автоматизация снабжения</p>
      </div>

      <button class="btn-create-primary" @click="isFormOpen = true">
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>Создать заявку</span>
      </button>
    </header>

    <!-- Реестр и фильтры -->
    <section class="registry-section">
      <div class="registry-header">
        <div class="title-wrap">
          <h2>Реестр заявок</h2>
        </div>

        <div class="filters-bar">
          <div class="filter-controls">
            <div class="filter-group">
              <label>Дата поставки:</label>
              <input type="date" v-model="selectedDate" class="filter-input" />
              <button :class="['btn-quick-date', { active: isTodaySelected }]" @click="setToday">
                Сегодня
              </button>
              <button :class="['btn-quick-date', { active: selectedDate === '' }]" @click="selectedDate = ''">
                Все
              </button>
            </div>

            <div class="filter-group">
              <label>Приоритет:</label>
              <select v-model="selectedPriority" class="filter-select">
                <option value="ALL">Все приоритеты</option>
                <option value="CRITICAL">Критичный</option>
                <option value="MEDIUM">Средний</option>
                <option value="LOW">Низкий</option>
              </select>
            </div>
          </div>

          <!-- Динамический счетчик позиций с правильным склонением -->
          <span class="count-badge">{{ formattedPositionsCount }}</span>
        </div>
      </div>

      <!-- Реестр заявок -->
      <SupplyList 
        :requests="filteredRequests" 
        :loading="loading" 
        :pendingDeleteIds="pendingDelete ? [pendingDelete.id] : []"
        @request-delete="initiateDelete" 
      />
    </section>

    <!-- Выдвижная панель с формой (Drawer) -->
    <Teleport to="body">
      <Transition name="drawer">
        <div v-if="isFormOpen" class="drawer-overlay" @click.self="isFormOpen = false">
          <div class="drawer-content">
            <SupplyForm @create="handleCreate" @close="isFormOpen = false" />
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Уведомления (Toast) -->
    <Teleport to="body">
      <ToastNotification 
        v-model="toast.show" 
        :title="toast.title" 
        :message="toast.message" 
        :type="toast.type" 
      />
    </Teleport>

    <!-- Telegram-Style Undo Delete Toast -->
    <Teleport to="body">
      <DeleteUndoToast 
        :show="!!pendingDelete" 
        :title="pendingDelete?.title || ''" 
        :duration="5"
        @undo="cancelDelete" 
        @timeout="confirmDelete" 
      />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { supplyApi } from '../api/supplyApi.js'
import { pluralize } from '../utils/formatters.js'
import SupplyForm from '../components/SupplyForm.vue'
import SupplyList from '../components/SupplyList.vue'
import ToastNotification from '../components/ToastNotification.vue'
import DeleteUndoToast from '../components/DeleteUndoToast.vue'

// Функция локальной даты YYYY-MM-DD (без сдвига по UTC)
const getTodayString = () => {
  const date = new Date()
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const requests = ref([])
const loading = ref(false)
const isFormOpen = ref(false)
const selectedDate = ref(getTodayString())
const selectedPriority = ref('ALL')

// Состояние отложенного удаления (Telegram Undo)
const pendingDelete = ref(null)

const toast = reactive({
  show: false,
  title: '',
  message: '',
  type: 'success'
})

const showToast = (title, message, type = 'success') => {
  toast.title = title
  toast.message = message
  toast.type = type
  toast.show = true
}

const isTodaySelected = computed(() => selectedDate.value === getTodayString())
const setToday = () => { selectedDate.value = getTodayString() }

const loadRequests = async () => {
  loading.value = true
  try {
    requests.value = await supplyApi.getAll()
  } catch (err) {
    console.error('Ошибка загрузки заявок:', err)
    showToast('Ошибка загрузки', 'Не удалось получить список заявок с сервера', 'error')
  } finally {
    loading.value = false
  }
}

// Фильтрация списка заявок (моментально скрывает элемент при подготовке к удалению)
const filteredRequests = computed(() => {
  return requests.value.filter(item => {
    if (pendingDelete.value && item.id === pendingDelete.value.id) {
      return false
    }

    const itemDate = item.createdAt ? item.createdAt.split('T')[0] : item.date
    const matchesDate = !selectedDate.value || itemDate === selectedDate.value
    const matchesPriority = selectedPriority.value === 'ALL' || item.priority === selectedPriority.value
    return matchesDate && matchesPriority
  })
})

// Склонение слова "позиция" для баджа
const formattedPositionsCount = computed(() => {
  const count = filteredRequests.value.length
  return `${count} ${pluralize(count, ['позиция', 'позиции', 'позиций'])}`
})

/* --- СОЗДАНИЕ ЗАЯВКИ --- */
const handleCreate = async (newRequestData, resetFormCallback) => {
  try {
    await supplyApi.create({
      title: newRequestData.title,
      site: newRequestData.object,
      object: newRequestData.object,
      quantity: Number(newRequestData.amount),
      amount: Number(newRequestData.amount),
      unit: newRequestData.unit,
      priority: newRequestData.priority,
      deliveryTimeStart: newRequestData.deliveryTimeStart,
      deliveryTimeEnd: newRequestData.deliveryTimeEnd,
      unloadingEquipment: newRequestData.unloadingEquipment
    })

    await loadRequests()
    
    // Сбрасываем форму и закрываем Drawer при успешном создании
    if (typeof resetFormCallback === 'function') {
      resetFormCallback()
    }
    isFormOpen.value = false

    showToast('Заявка создана!', `Позиция "${newRequestData.title}" добавлена в реестр.`, 'success')
  } catch (err) {
    console.error('Ошибка создания:', err)
    // Пробрасываем точечное сообщение об ошибке с бэкенда
    showToast('Ошибка сохранения', err.message || 'Не удалось сохранить заявку', 'error')
  }
}

/* --- TELEGRAM UNDO DELETE --- */
const initiateDelete = (item) => {
  // Если у нас уже был кандидат на удаление, подтверждаем его перед запуском нового
  if (pendingDelete.value) {
    confirmDelete()
  }
  pendingDelete.value = item
}

const cancelDelete = () => {
  pendingDelete.value = null
}

const confirmDelete = async () => {
  if (!pendingDelete.value) return

  const itemToDelete = pendingDelete.value

  try {
    await supplyApi.delete(itemToDelete.id)
    await loadRequests()
  } catch (err) {
    console.error('Ошибка при удалении:', err)
    showToast('Ошибка удаления', err.message || 'Не удалось удалить заявку с сервера', 'error')
    await loadRequests()
  } finally {
    if (pendingDelete.value?.id === itemToDelete.id) {
      pendingDelete.value = null
    }
  }
}

onMounted(loadRequests)
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.page-container {
  max-width: 960px;
  margin: 0 auto;

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-8);

    .header-text {
      h1 { font-size: 1.5rem; font-weight: 800; color: var(--color-text-main); margin: 0; }
      p { color: var(--color-text-muted); font-size: 0.9rem; margin: var(--space-1) 0 0 0; }
    }

    .btn-create-primary {
      background: var(--color-primary);
      color: var(--color-primary-contrast);
      border: none;
      padding: var(--space-2) var(--space-5);
      border-radius: var(--radius-md);
      font-weight: 700;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: var(--space-2);
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(var(--color-primary-rgb), 0.25);
      transition: all var(--transition-base);

      .btn-icon { width: 18px; height: 18px; }

      &:hover {
        background: var(--color-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--color-primary-rgb), 0.35);
      }
    }
  }

  .registry-section {
    .registry-header {
      display: flex;
      flex-direction: column;
      gap: var(--space-4);
      margin-bottom: var(--space-5);

      .title-wrap {
        h2 { font-size: 1.25rem; font-weight: 800; color: var(--color-text-main); margin: 0; }
      }

      .filters-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-5);
        background: var(--color-surface);
        padding: var(--space-3) var(--space-5);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);

        .filter-controls {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          gap: var(--space-5);
        }

        .filter-group {
          display: flex;
          align-items: center;
          gap: var(--space-2);

          label { font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted); }

          .filter-input, .filter-select {
            padding: var(--space-1) var(--space-2);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            outline: none;
            color: var(--color-text-main);
            background: var(--color-surface);
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);

            &:focus { 
              border-color: var(--color-border-focus);
              box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.15);
            }
          }

          .btn-quick-date {
            background: var(--color-bg-secondary);
            border: none;
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--color-text-muted);
            cursor: pointer;
            transition: all var(--transition-fast);

            &:hover { background: var(--color-bg-tertiary); }
            &.active { 
              background: var(--color-primary); 
              color: var(--color-primary-contrast); 
            }
          }
        }

        .count-badge {
          margin-left: auto;
          background: var(--color-primary-light);
          color: var(--color-primary);
          font-size: 0.8rem;
          font-weight: 700;
          padding: var(--space-1) var(--space-3);
          border-radius: var(--radius-full);
          white-space: nowrap;
        }
      }
    }
  }
}

/* Стили выкатной панели Drawer */
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(2px);
  z-index: var(--z-modal-backdrop);
  display: flex;
  justify-content: flex-end;
}

.drawer-content {
  width: 100%;
  max-width: 520px;
  height: 100vh;
  background: var(--color-surface);
  box-shadow: var(--shadow-xl);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: var(--space-6);
  box-sizing: border-box;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: opacity var(--transition-base);
  .drawer-content {
    transition: transform var(--transition-slow);
  }
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
  .drawer-content {
    transform: translateX(100%);
  }
}
</style>
