<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'info',
    validator: (v) => ['info', 'success', 'warning', 'danger'].includes(v),
  },
  title: { type: String, default: '' },
})

const classes = computed(() => {
  const base = 'rounded-lg border px-4 py-3 text-sm'

  const variants = {
    info: 'border-border bg-surface text-foreground',
    success: 'border-success/30 bg-success/10 text-success',
    warning: 'border-warning/30 bg-warning/10 text-warning',
    danger: 'border-danger/30 bg-danger/10 text-danger',
  }

  return [base, variants[props.variant]]
})
</script>

<template>
  <div :class="classes" role="alert">
    <p v-if="title" class="mb-1 font-semibold">{{ title }}</p>
    <slot />
  </div>
</template>
