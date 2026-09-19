<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import AppCard from '@/components/AppCard.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <AppLayout>
    <h1>Dashboard</h1>
    <p class="mt-2 text-muted">Welcome, {{ auth.user?.name }}.</p>

    <AppCard class="mt-8">
      <p class="text-sm text-muted">
        Saved opportunities and application tracking will appear here in Phase 4.
      </p>
    </AppCard>
  </AppLayout>
</template>
