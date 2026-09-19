<script setup>
defineProps({
  id: { type: String, default: undefined },
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  modelValue: { type: [String, Number], default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  autocomplete: { type: String, default: undefined },
  required: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label :for="id" class="text-sm font-medium text-foreground">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :autocomplete="autocomplete"
      :required="required"
      class="w-full rounded-md border border-border bg-surface px-3 py-2.5 text-foreground shadow-sm transition-colors placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="text-sm text-danger">{{ error }}</p>
    <p v-else-if="hint" class="text-sm text-muted">{{ hint }}</p>
  </div>
</template>
