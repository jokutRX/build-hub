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
  bottom: 24px;
  right: 24px;
  z-index: 2000;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 300px;
  max-width: 420px;
  padding: 1rem 1.25rem;
  border-radius: 10px;
  background: #ffffff;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  border-left: 5px solid;

  &.success {
    border-left-color: #22c55e;
    .toast-icon { background: #dcfce7; color: #15803d; }
  }

  &.error {
    border-left-color: #ef4444;
    .toast-icon { background: #fee2e2; color: #b91c1c; }
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
      color: #0f172a;
      margin-bottom: 0.15rem;
    }

    .toast-message {
      font-size: 0.8rem;
      color: #64748b;
    }
  }

  .toast-close {
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 1rem;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    transition: all 0.2s;

    &:hover {
      color: #0f172a;
      background: #f1f5f9;
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