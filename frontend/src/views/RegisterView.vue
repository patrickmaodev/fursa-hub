<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import AppAlert from '@/components/AppAlert.vue'
import AppButton from '@/components/AppButton.vue'
import AppInput from '@/components/AppInput.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const error = ref('')
const fieldErrors = ref({})

async function submit() {
  error.value = ''
  fieldErrors.value = {}
  try {
    await auth.register(form)
    await router.push('/dashboard')
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = e.response.data.errors ?? {}
      error.value = e.response.data.message ?? 'Validation failed.'
    } else {
      error.value = 'Could not create account.'
    }
  }
}
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-md">
      <h1>Create account</h1>
      <p class="mt-2 text-muted">Join Fursa Hub to save and track opportunities.</p>

      <AppAlert v-if="error" variant="danger" class="mt-6" :title="error" />

      <form class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
        <AppInput
          id="register-name"
          v-model="form.name"
          label="Name"
          autocomplete="name"
          required
          :error="fieldErrors.name?.[0]"
        />
        <AppInput
          id="register-email"
          v-model="form.email"
          label="Email"
          type="email"
          autocomplete="email"
          required
          :error="fieldErrors.email?.[0]"
        />
        <AppInput
          id="register-password"
          v-model="form.password"
          label="Password"
          type="password"
          autocomplete="new-password"
          required
          :error="fieldErrors.password?.[0]"
        />
        <AppInput
          id="register-password-confirm"
          v-model="form.password_confirmation"
          label="Confirm password"
          type="password"
          autocomplete="new-password"
          required
        />
        <AppButton type="submit">Register</AppButton>
      </form>

      <p class="mt-6 text-sm text-muted">
        Already have an account?
        <RouterLink to="/login">Log in</RouterLink>
      </p>
    </div>
  </AppLayout>
</template>
