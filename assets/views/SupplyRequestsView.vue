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
        <span>Создать</span>
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
              <input type="date" v-model="selectedDate" class="filter-input" autocomplete="off" />
              <button :class="['btn-quick-date', { active: isTodaySelected }]" @click="setToday">
                Сегодня
              </button>
              <button :class="['btn-quick-date', { active: selectedDate === '' }]" @click="selectedDate = ''">
                Все
              </button>
            </div>

            <div class="filter-group">
              <label>Приоритет:</label>
              <select v-model="selectedPriority" class="filter-select" autocomplete="off">
                <option value="ALL">Все приоритеты</option>
                <option value="CRITICAL">Критичный</option>
                <option value="MEDIUM">Средний</option>
                <option value="LOW">Низкий</option>
              </select>
            </div>
          </div>

          <!-- Динамический счетчик позиций -->
          <span class="count-badge">{{ formattedPositionsCount }}</span>
        </div>
      </div>

      <!-- Реестр заявок -->
      <SupplyList 
        :requests="filteredRequests" 
        :loading="loading" 
        :pendingDeleteIds="pendingDelete ? [pendingDelete.id] : []"
        :selectedIds="selectedIds"
        @request-delete="initiateDelete"
        @duplicate="handleDuplicate"
        @select-item="toggleSelect"
      />
    </section>

    <!-- Плавающая панель выбора -->
    <Transition name="bulk-bar">
      <div v-if="selectedIds.size > 0" class="bulk-floating-bar">
        <div class="bulk-info">
          <span class="count">{{ selectedIds.size }} выбрано</span>
          <button class="close-btn" @click="selectedIds.clear()">✕</button>
        </div>
        <div class="bulk-actions">
          <button class="btn-bulk" @click="bulkAction('IN_TRANSIT')">В доставку</button>
          <button class="btn-bulk" @click="bulkAction('COMPLETED')">Завершить</button>
          <button class="btn-bulk-primary" @click="mergeRequests">⚡ Объединить в рейс</button>
        </div>
        <div class="bulk-total">
          Итого: {{ totalWeight }} т ({{ totalCost }} ₽)
        </div>
      </div>
    </Transition>

    <!-- Выдвижная панель с формой (Drawer) -->
    <Teleport to="body">
      <Transition name="drawer">
        <div v-if="isFormOpen" class="drawer-overlay" @click.self="closeForm">
          <div class="drawer-content">
            <SupplyForm 
              ref="supplyFormRef"
              @create="handleCreate" 
              @close="closeForm" 
            />
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
const supplyFormRef = ref(null)

const closeForm = () => {
  isFormOpen.value = false
}

const handleDuplicate = (item) => {
  isFormOpen.value = true
  setTimeout(() => {
    if (supplyFormRef.value) {
      supplyFormRef.value.populateFromTemplate(item)
    }
  }, 100)
}

const selectedDate = ref(getTodayString())
const selectedPriority = ref('ALL')
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

const selectedIds = ref(new Set())
const lastSelectedIndex = ref(null)

const toggleSelect = (item, index, event) => {
  if (event && event.shiftKey && lastSelectedIndex.value !== null) {
    const start = Math.min(index, lastSelectedIndex.value)
    const end = Math.max(index, lastSelectedIndex.value)
    
    for (let i = start; i <= end; i++) {
      const id = filteredRequests.value[i]?.id
      if (id) selectedIds.value.add(id)
    }
  } else {
    if (selectedIds.value.has(item.id)) {
      selectedIds.value.delete(item.id)
    } else {
      selectedIds.value.add(item.id)
    }
  }
  lastSelectedIndex.value = index
}

const filteredRequests = computed(() => {
  return requests.value.filter(item => {
    if (pendingDelete.value && item.id === pendingDelete.value.id) return false
    const itemDate = item.createdAt ? item.createdAt.split('T')[0] : item.date
    const matchesDate = !selectedDate.value || itemDate === selectedDate.value
    const matchesPriority = selectedPriority.value === 'ALL' || item.priority === selectedPriority.value
    return matchesDate && matchesPriority
  })
})

const formattedPositionsCount = computed(() => {
  const count = filteredRequests.value.length
  return `${count} ${pluralize(count, ['позиция', 'позиции', 'позиций'])}`
})

const totalWeight = computed(() => {
  return requests.value
    .filter(item => selectedIds.value.has(item.id))
    .reduce((sum, item) => sum + (item.quantity || 0), 0)
    .toFixed(2)
})

const totalCost = computed(() => {
  return requests.value
    .filter(item => selectedIds.value.has(item.id))
    .reduce((sum, item) => {
      const price = (item.quantity > 15) ? 7000 : 3000
      return sum + price
    }, 0)
    .toLocaleString('ru-RU')
})

const bulkAction = (status) => {
  showToast('Успешно', `Статус ${status} применен к ${selectedIds.value.size} заявкам`)
  selectedIds.value.clear()
}

const mergeRequests = () => {
  const selected = requests.value.filter(i => selectedIds.value.has(i.id))
  if (selected.length === 0) return

  const firstObject = selected[0].object || selected[0].site
  const isSameObject = selected.every(i => (i.object || i.site) === firstObject)

  if (!isSameObject) {
    showToast('Ошибка', 'Объединение возможно только для одного объекта', 'error')
    return
  }
  
  showToast('Рейс сформирован', `Заявки объединены в 1 рейс. Вес: ${totalWeight.value} т`, 'success')
  selectedIds.value.clear()
}

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
    if (typeof resetFormCallback === 'function') resetFormCallback()
    isFormOpen.value = false
    showToast('Заявка создана!', `Позиция "${newRequestData.title}" добавлена в реестр.`, 'success')
  } catch (err) {
    showToast('Ошибка сохранения', err.message || 'Не удалось сохранить заявку', 'error')
  }
}

const initiateDelete = (item) => {
  if (pendingDelete.value) confirmDelete()
  pendingDelete.value = item
}

const cancelDelete = () => { pendingDelete.value = null }

const confirmDelete = async () => {
  if (!pendingDelete.value) return
  const itemToDelete = pendingDelete.value
  try {
    await supplyApi.delete(itemToDelete.id)
    await loadRequests()
  } catch (err) {
    showToast('Ошибка удаления', 'Не удалось удалить заявку', 'error')
    await loadRequests()
  } finally {
    if (pendingDelete.value?.id === itemToDelete.id) pendingDelete.value = null
  }
}

const loadRequests = async () => {
  loading.value = true
  try {
    requests.value = await supplyApi.getAll()
  } catch (err) {
    showToast('Ошибка загрузки', 'Не удалось получить список заявок', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(loadRequests)
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

.page-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--space-8) var(--space-6);

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-10);

    .header-text {
      h1 { font-size: 1.8rem; font-weight: 800; color: var(--color-text-main); margin: 0; }
      p { color: var(--color-text-muted); font-size: 1rem; margin: var(--space-2) 0 0 0; }
    }

    .btn-create-primary {
      background: var(--color-primary);
      color: var(--color-primary-contrast);
      border: none;
      padding: var(--space-3) var(--space-6);
      border-radius: var(--radius-lg);
      font-weight: 700;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: var(--space-3);
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(var(--color-primary-rgb), 0.25);
      transition: all var(--transition-base);

      .btn-icon { width: 18px; height: 18px; }

      &:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(var(--color-primary-rgb), 0.35);
      }
    }
  }

  .registry-section {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);

    .registry-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--space-6);
      margin-bottom: var(--space-6);

      .title-wrap {
        h2 { font-size: 1.5rem; font-weight: 800; color: var(--color-text-main); margin: 0; }
      }

      .filters-bar {
        display: flex;
        align-items: center;
        gap: var(--space-6);
        background: var(--color-bg-secondary);
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-lg);

        .count-badge {
          font-size: 0.85rem;
          font-weight: 700;
          color: var(--color-primary);
          background: rgba(var(--color-primary-rgb), 0.1);
          padding: 4px 10px;
          border-radius: var(--radius-md);
        }

        .filter-controls {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          gap: var(--space-4);
        }

        .filter-group {
          display: flex;
          align-items: center;
          gap: var(--space-3);

          label { font-size: 0.85rem; font-weight: 700; color: var(--color-text-muted); }

          .filter-input, .filter-select {
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            outline: none;
            color: var(--color-text-main);
            background: var(--color-surface);
            transition: all var(--transition-fast);
          }

          .btn-quick-date {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-text-muted);
            cursor: pointer;
            
            &.active {
              background: var(--color-primary);
              color: var(--color-primary-contrast);
              border-color: var(--color-primary);
            }
          }
        }
      }
    }
  }
}

.bulk-floating-bar {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  color: var(--color-text-main);
  padding: 10px 16px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: var(--shadow-lg);
  z-index: 1000;
  
  .bulk-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 0.9rem;
  }

  .close-btn {
    background: var(--color-bg-secondary);
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    &:hover { background: var(--color-critical-bg); color: var(--color-critical); }
  }

  .bulk-actions { display: flex; gap: 8px; }
  .btn-bulk { background: var(--color-bg-secondary); border: 1px solid var(--color-border); color: var(--color-text-secondary); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; }
  .btn-bulk-primary { background: var(--color-primary); border: none; color: var(--color-primary-contrast); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 700; }
  .bulk-total { font-size: 0.85rem; font-weight: 700; color: var(--color-primary); padding-left: 10px; border-left: 1px solid var(--color-border); }
}

.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translate(-50%, 20px); }

/* Выдвижная панель (Drawer) */
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

/* Стили для плавной анимации выдвижения */
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.3s ease;

  .drawer-content {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
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