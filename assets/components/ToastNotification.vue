<template>
  <Transition name="toast">
    <div v-if="show" :class="['toast-container', type]">
      <div class="toast-icon">
        <span v-if="type === 'success'">✓</span>
        <span v-else>✕</span>
      </div>

      <div class="toast-content">
        <div class="toast-title">{{ title }}</div>
        <div class="toast-message">{{ message }}</div>
      </div>

      <button class="toast-close" @click="close">✕</button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  type: { type: String, default: 'success' }, // 'success' | 'error'
  duration: { type: Number, default: 5000 }
})

const emit = defineEmits(['update:modelValue'])

const show = ref(props.modelValue)
let timer = null

const close = () => {
  show.value = false
  emit('update:modelValue', false)
  if (timer) clearTimeout(timer)
}

watch(() => props.modelValue, (newVal) => {
  show.value = newVal
  if (newVal) {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>

<style lang="scss" scoped>
.toast-container {
  position: fixed;
  bottom: var(--space-6);
  right: var(--space-6);
  z-index: var(--z-toast);
  display: flex;
  align-items: center;
  gap: var(--space-3);
  min-width: 300px;
  max-width: 420px;
  padding: var(--space-4) var(--space-5);
  border-radius: var(--radius-lg);
  background: var(--color-surface);
  box-shadow: var(--shadow-lg);
  border-left: 5px solid;

  &.success {
    border-left-color: var(--color-success);
    .toast-icon { background: var(--color-success-bg); color: var(--color-success); }
  }

  &.error {
    border-left-color: var(--color-error);
    .toast-icon { background: var(--color-error-bg); color: var(--color-error); }
  }

  .toast-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
  }

  .toast-content {
    flex: 1;

    .toast-title {
      font-weight: 700;
      font-size: 0.9rem;
      color: var(--color-text-main);
      margin-bottom: var(--space-1);
    }

    .toast-message {
      font-size: 0.8rem;
      color: var(--color-text-muted);
    }
  }

  .toast-close {
    background: transparent;
    border: none;
    color: var(--color-text-light);
    cursor: pointer;
    font-size: 1rem;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-xs);
    transition: all var(--transition-fast);

    &:hover {
      color: var(--color-text-main);
      background: var(--color-bg-secondary);
    }
  }
}

/* Анимация всплытия из правого нижнего угла */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
