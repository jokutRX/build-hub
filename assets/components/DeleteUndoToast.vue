<template>
  <Transition name="toast">
    <div v-if="show" class="undo-toast">
      <div class="toast-left">
        <!-- Анимированный SVG лоадер вокруг таймера -->
        <div class="timer-progress">
          <svg class="progress-ring" width="30" height="30" viewBox="0 0 30 30">
            <circle
              class="progress-ring__background"
              stroke="#fecdd3"
              stroke-width="2.5"
              fill="transparent"
              r="12"
              cx="15"
              cy="15"
            />
            <circle
              class="progress-ring__circle"
              stroke="#ef4444"
              stroke-width="2.5"
              stroke-linecap="round"
              fill="transparent"
              r="12"
              cx="15"
              cy="15"
              :style="{ strokeDashoffset: dashOffset, strokeDasharray: circumference }"
            />
          </svg>
          <span class="timer-number">{{ secondsLeft }}</span>
        </div>

        <span class="toast-text">
          Заявка <strong>«{{ title }}»</strong> удаляется...
        </span>
      </div>

      <button class="btn-undo" @click="$emit('undo')">
        Отменить
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  duration: { type: Number, default: 5 } // Длительность в секундах
})

const emit = defineEmits(['undo', 'timeout'])

const secondsLeft = ref(props.duration)
let interval = null

// Расчет параметров кругового SVG прогресс-бара
const radius = 12
const circumference = 2 * Math.PI * radius // ~75.39

const dashOffset = computed(() => {
  const progress = secondsLeft.value / props.duration
  return circumference * (1 - progress)
})

const startTimer = () => {
  clearInterval(interval)
  secondsLeft.value = props.duration

  interval = setInterval(() => {
    secondsLeft.value -= 1
    if (secondsLeft.value <= 0) {
      clearInterval(interval)
      emit('timeout')
    }
  }, 1000)
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    startTimer()
  } else {
    clearInterval(interval)
  }
})

onUnmounted(() => {
  clearInterval(interval)
})
</script>

<style lang="scss" scoped>

.undo-toast {
  position: fixed;
  bottom: var(--space-6);
  right: var(--space-6);
  z-index: var(--z-toast);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-6);
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-xl);
  background: var(--color-surface);
  color: var(--color-text-main);
  border: 1px solid var(--color-border);
  border-left: 4px solid var(--color-critical);
  box-shadow: var(--shadow-lg);
  white-space: nowrap;

  .toast-left {
    display: flex;
    align-items: center;
    gap: var(--space-3);

    .timer-progress {
      position: relative;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;

      .progress-ring {
        position: absolute;
        inset: 0;
        transform: rotate(-90deg);

        .progress-ring__background {
          stroke: var(--color-critical-bg);
        }

        .progress-ring__circle {
          stroke: var(--color-critical);
          transition: stroke-dashoffset 1s linear;
        }
      }

      .timer-number {
        font-weight: 800;
        font-size: 0.8rem;
        color: var(--color-critical);
        line-height: 1;
      }
    }

    .toast-text {
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--color-text-muted);

      strong {
        font-weight: 700;
        color: var(--color-text-main);
      }
    }
  }

  .btn-undo {
    background: var(--color-primary-light);
    border: none;
    color: var(--color-primary);
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-md);
    white-space: nowrap;
    transition: all var(--transition-fast);
    flex-shrink: 0;

    &:hover {
      background: var(--color-primary-light);
      opacity: 0.9;
    }
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
