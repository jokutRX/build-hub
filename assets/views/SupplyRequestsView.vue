<template>
  <div class="page-container">
    <!-- Шапка страницы с кнопкой вызова формы -->
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

    <!-- Секция Реестра с фильтрами -->
    <section class="registry-section">
      <div class="registry-header">
        <div class="title-wrap">
          <h2>Реестр заявок</h2>
          <span class="count-badge">{{ filteredRequests.length }} позиций</span>
        </div>

        <div class="filters-bar">
          <div class="filter-group">
            <label>Дата поставки:</label>
            <input type="date" v-model="selectedDate" class="filter-input" />
            <button 
              :class="['btn-quick-date', { active: isTodaySelected }]" 
              @click="setToday"
            >
              Сегодня
            </button>
            <button 
              :class="['btn-quick-date', { active: selectedDate === '' }]" 
              @click="selectedDate = ''"
            >
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
      </div>

      <!-- Список заявок -->
      <SupplyList 
        :requests="filteredRequests" 
        :loading="loading" 
        @delete-request="handleDelete" 
      />
    </section>

    <!-- ВЫДВИЖНАЯ ПАНЕЛЬ С ФОРМОЙ (DRAWER) -->
    <Teleport to="body">
      <Transition name="drawer">
        <div v-if="isFormOpen" class="drawer-overlay" @click.self="isFormOpen = false">
          <div class="drawer-content">
            <SupplyForm 
              @create="handleCreate" 
              @close="isFormOpen = false" 
            />
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- УВЕДОМЛЕНИЯ (TOAST) -->
    <Teleport to="body">
      <ToastNotification 
        v-model="toast.show" 
        :title="toast.title" 
        :message="toast.message" 
        :type="toast.type" 
        :duration="5000" 
      />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { supplyApi } from '../api/supplyApi.js'
import SupplyForm from '../components/SupplyForm.vue'
import SupplyList from '../components/SupplyList.vue'
import ToastNotification from '../components/ToastNotification.vue'

const getTodayString = () => new Date().toISOString().split('T')[0]

const requests = ref([])
const loading = ref(false)
const isFormOpen = ref(false)
const selectedDate = ref(getTodayString())
const selectedPriority = ref('ALL')

// Состояние для тоаста
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

const setToday = () => {
  selectedDate.value = getTodayString()
}

const loadRequests = async () => {
  loading.value = true
  try {
    requests.value = await supplyApi.getAll()
  } catch (err) {
    console.error('Ошибка при загрузке заявок:', err)
    showToast('Ошибка загрузки', 'Не удалось получить список заявок с сервера', 'error')
  } finally {
    loading.value = false
  }
}

const filteredRequests = computed(() => {
  return requests.value.filter(item => {
    const matchesDate = !selectedDate.value || item.createdAt?.startsWith(selectedDate.value) || item.date === selectedDate.value
    const matchesPriority = selectedPriority.value === 'ALL' || item.priority === selectedPriority.value
    return matchesDate && matchesPriority
  })
})

const handleCreate = async (newRequestData) => {
  try {
    await supplyApi.create({
      title: newRequestData.title,
      site: newRequestData.object,
      quantity: newRequestData.amount,
      unit: newRequestData.unit,
      priority: newRequestData.priority
    })

    await loadRequests()
    isFormOpen.value = false
    
    // Успешный тоаст
    showToast(
      'Заявка создана!', 
      `Позиция "${newRequestData.title}" успешно добавлена в реестр.`, 
      'success'
    )
  } catch (err) {
    console.error('Ошибка при создании:', err)
    
    // Ошибка тоаст
    const errorMsg = err.response?.data?.error || 'Не удалось сохранить заявку. Проверьте данные.'
    showToast('Ошибка сохранения', errorMsg, 'error')
  }
}

const handleDelete = async (id) => {
  try {
    await supplyApi.delete(id)
    await loadRequests()
    showToast('Удалено', 'Заявка успешно удалена из системы', 'success')
  } catch (err) {
    showToast('Ошибка удаления', 'Не удалось удалить заявку', 'error')
  }
}

onMounted(loadRequests)
</script>

<style lang="scss" scoped>
@use "sass:color";
@use "../styles/main.scss" as *;

.page-container {
  max-width: 960px;
  margin: 0 auto;

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;

    .header-text {
      h1 { font-size: 1.5rem; font-weight: 800; color: $text-main; margin: 0; }
      p { color: $text-muted; font-size: 0.9rem; margin: 0.25rem 0 0 0; }
    }

    .btn-create-primary {
      background: $primary;
      color: #ffffff;
      border: none;
      padding: 0.65rem 1.25rem;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
      transition: all 0.2s ease;

      .btn-icon { width: 18px; height: 18px; }

      &:hover {
        background: color.adjust(#2563eb, $lightness: -5%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
      }
    }
  }

  .registry-section {
    .registry-header {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin-bottom: 1.25rem;

      .title-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;

        h2 { font-size: 1.2rem; font-weight: 800; color: $text-main; margin: 0; }
        .count-badge {
          background: #eff6ff;
          color: $primary;
          font-size: 0.75rem;
          font-weight: 700;
          padding: 0.2rem 0.6rem;
          border-radius: 12px;
        }
      }

      .filters-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        background: #ffffff;
        padding: 0.85rem 1.25rem;
        border: 1px solid $border;
        border-radius: 10px;

        .filter-group {
          display: flex;
          align-items: center;
          gap: 0.5rem;

          label { font-size: 0.8rem; font-weight: 700; color: $text-muted; }

          .filter-input, .filter-select {
            padding: 0.4rem 0.6rem;
            border: 1px solid $border;
            border-radius: 6px;
            font-size: 0.85rem;
            outline: none;
            color: $text-main;
            &:focus { border-color: $primary; }
          }

          .btn-quick-date {
            background: #f1f5f9;
            border: none;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: $text-muted;
            cursor: pointer;
            transition: all 0.2s;

            &:hover { background: #e2e8f0; }
            &.active { background: $primary; color: #fff; }
          }
        }
      }
    }
  }
}

/* СТИЛИ ВЫДВИЖНОЙ ПАНЕЛИ (DRAWER) */
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(2px);
  z-index: 1000;
  display: flex;
  justify-content: flex-end;
}

.drawer-content {
  width: 100%;
  max-width: 520px;
  height: 100vh;
  background: #ffffff;
  box-shadow: -10px 0 25px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: 1.5rem;
  box-sizing: border-box;
}

/* Анимация появления drawer */
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.25s ease;

  .drawer-content {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
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