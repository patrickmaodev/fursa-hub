<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v) =>
      ['default', 'featured', 'success', 'warning', 'danger', 'secondary', 'muted'].includes(v),
  },
})

const classes = computed(() => {
  const base = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold'

  const variants = {
    default: 'bg-background text-foreground ring-1 ring-border',
    featured: 'bg-accent/20 text-foreground ring-1 ring-accent/40',
    success: 'bg-success/15 text-success',
    warning: 'bg-warning/15 text-warning',
    danger: 'bg-danger/15 text-danger',
    secondary: 'bg-secondary/15 text-secondary',
    muted: 'bg-background text-muted',
  }

  return [base, variants[props.variant]]
})
</script>

<template>
  <span :class="classes">
    <slot />
  </span>
</template>
