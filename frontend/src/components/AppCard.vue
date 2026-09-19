<script setup>
defineProps({
  padding: {
    type: String,
    default: 'md',
    validator: (v) => ['none', 'sm', 'md', 'lg'].includes(v),
  },
  hoverable: { type: Boolean, default: false },
})

const paddingClass = {
  none: '',
  sm: 'p-4',
  md: 'p-5',
  lg: 'p-6',
}
</script>

<template>
  <article
    :class="[
      'rounded-lg border border-border bg-surface shadow-sm',
      paddingClass[padding],
      hoverable && 'transition-shadow hover:shadow-md',
    ]"
  >
    <slot />
  </article>
</template>
