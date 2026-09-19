<script setup>
import { onMounted, ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AppAlert from '@/components/AppAlert.vue'
import AppCard from '@/components/AppCard.vue'
import api, { ensureCsrfCookie } from '@/services/api'

const message = ref('')
const error = ref('')

onMounted(async () => {
  try {
    await ensureCsrfCookie()
    const { data } = await api.get('/api/admin/ping')
    message.value = data.message
  } catch {
    error.value = 'Could not reach admin API.'
  }
})
</script>

<template>
  <AdminLayout>
    <h1>Dashboard</h1>
    <p class="mt-2 text-muted">Manage opportunities, organizations, and users (Phase 5).</p>

    <AppAlert v-if="message" variant="success" class="mt-6">{{ message }}</AppAlert>
    <AppAlert v-if="error" variant="danger" class="mt-6">{{ error }}</AppAlert>

    <AppCard class="mt-8">
      <p class="text-sm text-muted">Moderation queues and CRUD screens will be added here.</p>
    </AppCard>
  </AdminLayout>
</template>
