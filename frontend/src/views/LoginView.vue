<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
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
  remember: false,
})

const error = ref('')
const fieldErrors = ref({})

async function submit() {
  error.value = ''
  fieldErrors.value = {}
  try {
    await auth.login(form)
    if (auth.isAdmin) {
      await auth.logout()
      error.value = 'Please use the appropriate sign-in page for this account.'
      return
    }
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await router.push(redirect)
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = e.response.data.errors ?? {}
      error.value = e.response.data.message ?? 'Validation failed.'
    } else {
      error.value = 'Could not log in. Check your credentials.'
    }
  }
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-md">
      <h1>Log in</h1>
      <p class="mt-2 text-muted">Access your saved opportunities and dashboard.</p>

      <AppAlert v-if="error" variant="danger" class="mt-6" :title="error" />

      <form class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
        <AppInput
          id="login-email"
          v-model="form.email"
          label="Email"
          type="email"
          autocomplete="email"
          required
          :error="fieldErrors.email?.[0]"
        />
        <AppInput
          id="login-password"
          v-model="form.password"
          label="Password"
          type="password"
          autocomplete="current-password"
          required
          :error="fieldErrors.password?.[0]"
        />
        <label class="flex items-center gap-2 text-sm text-foreground">
          <input v-model="form.remember" type="checkbox" class="rounded border-border" />
          Remember me
        </label>
        <AppButton type="submit">Log in</AppButton>
      </form>

      <p class="mt-6 text-sm text-muted">
        No account?
        <RouterLink to="/register">Register</RouterLink>
      </p>
      <p class="mt-4 text-sm text-muted">
        Platform staff:
        <RouterLink :to="{ name: 'admin-login', query: form.email ? { email: form.email } : {} }">
          Staff sign in
        </RouterLink>
      </p>
    </div>
  </AppLayout>
</template>
