import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { ensureCsrfCookie } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const bootstrapped = ref(false)
  const loading = ref(false)

  const isAuthenticated = computed(() => user.value !== null)
  const isAdmin = computed(() => Boolean(user.value?.is_admin))

  function setUserFromResponse(data) {
    user.value = data?.data ?? data ?? null
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/api/user')
      setUserFromResponse(data)
    } catch {
      user.value = null
    }
  }

  async function bootstrap() {
    if (bootstrapped.value) {
      return
    }
    loading.value = true
    try {
      await fetchUser()
    } finally {
      bootstrapped.value = true
      loading.value = false
    }
  }

  async function register(payload) {
    await ensureCsrfCookie()
    const { data } = await api.post('/api/register', payload)
    setUserFromResponse(data)
    return user.value
  }

  async function login(payload) {
    await ensureCsrfCookie()
    const { data } = await api.post('/api/login', payload)
    setUserFromResponse(data)
    return user.value
  }

  async function logout() {
    try {
      await ensureCsrfCookie()
      await api.post('/api/logout')
    } finally {
      user.value = null
    }
  }

  return {
    user,
    bootstrapped,
    loading,
    isAuthenticated,
    isAdmin,
    bootstrap,
    fetchUser,
    register,
    login,
    logout,
  }
})
