<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import AppButton from '@/components/AppButton.vue'

const open = defineModel({ type: Boolean, default: false })

defineProps({
  title: { type: String, default: '' },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
})

const sizeClass = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
}

function onKeydown(event) {
  if (event.key === 'Escape') {
    open.value = false
  }
}

watch(open, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      :aria-label="title || 'Dialog'"
    >
      <div class="absolute inset-0 bg-foreground/40 backdrop-blur-[2px]" @click="open = false" />
      <div
        :class="[
          'relative z-10 w-full rounded-lg border border-border bg-surface p-6 shadow-xl',
          sizeClass[size],
        ]"
      >
        <header v-if="title" class="mb-4 flex items-start justify-between gap-4">
          <h2 class="text-xl font-semibold text-foreground">{{ title }}</h2>
          <AppButton variant="ghost" size="sm" type="button" @click="open = false">
            Close
          </AppButton>
        </header>
        <slot />
        <footer v-if="$slots.footer" class="mt-6 flex flex-wrap justify-end gap-2">
          <slot name="footer" />
        </footer>
      </div>
    </div>
  </Teleport>
</template>
