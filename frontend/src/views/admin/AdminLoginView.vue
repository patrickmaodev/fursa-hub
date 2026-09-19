<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import AppAlert from '@/components/AppAlert.vue'
import AppButton from '@/components/AppButton.vue'
import AppInput from '@/components/AppInput.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({
  email: '',
  password: '',
})

const error = ref('')
const fieldErrors = ref({})

onMounted(() => {
  const email = route.query.email
  if (typeof email === 'string' && email) {
    form.email = email
  }
})

async function submit() {
  error.value = ''
  fieldErrors.value = {}
  try {
    await auth.login(form)
    if (!auth.isAdmin) {
      await auth.logout()
      error.value = 'This account does not have administrator access.'
      return
    }
    const redirect =
      typeof route.query.redirect === 'string' ? route.query.redirect : '/admin/dashboard'
    await router.push(redirect)
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = e.response.data.errors ?? {}
      error.value = e.response.data.message ?? 'Validation failed.'
    } else {
      error.value = 'Could not sign in. Check your credentials.'
    }
  }
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background">
    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-12">
      <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Fursa Hub</p>
      <h1 class="mt-2">Administrator sign in</h1>
      <p class="mt-2 text-sm text-muted">
        Admin accounts are provisioned on the server. There is no public admin registration.
      </p>

      <AppAlert v-if="error" variant="danger" class="mt-6" :title="error" />

      <form class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
        <AppInput
          id="admin-login-email"
          v-model="form.email"
          label="Email"
          type="email"
          autocomplete="email"
          required
          :error="fieldErrors.email?.[0]"
        />
        <AppInput
          id="admin-login-password"
          v-model="form.password"
          label="Password"
          type="password"
          autocomplete="current-password"
          required
          :error="fieldErrors.password?.[0]"
        />
        <AppButton type="submit">Sign in</AppButton>
      </form>

      <p class="mt-8 text-center text-sm text-muted">
        <RouterLink to="/">Back to public site</RouterLink>
      </p>
    </main>
  </div>
</template>
