<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppButton from '@/components/AppButton.vue'

const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
}
</script>

<template>
  <div class="flex min-h-screen w-full flex-col bg-background">
    <header
      class="flex shrink-0 flex-wrap items-center justify-between gap-4 border-b border-border bg-surface px-4 py-4 sm:px-6"
    >
      <RouterLink to="/" class="text-lg font-bold text-foreground no-underline hover:no-underline">
        Fursa Hub
      </RouterLink>
      <nav class="flex flex-wrap items-center gap-3 text-sm font-medium sm:gap-4">
        <RouterLink to="/" class="text-muted hover:text-foreground" active-class="!text-foreground">
          Home
        </RouterLink>
        <template v-if="auth.isAuthenticated">
          <RouterLink
            to="/dashboard"
            class="text-muted hover:text-foreground"
            active-class="!text-foreground"
          >
            Dashboard
          </RouterLink>
          <span class="hidden text-muted sm:inline">{{ auth.user?.name }}</span>
          <AppButton variant="ghost" size="sm" type="button" @click="handleLogout">
            Log out
          </AppButton>
        </template>
        <template v-else>
          <RouterLink to="/login" class="text-muted hover:text-foreground">Log in</RouterLink>
          <AppButton to="/register" size="sm">Register</AppButton>
        </template>
      </nav>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8 sm:px-6 sm:py-10">
      <slot />
    </main>

    <footer
      class="mt-auto shrink-0 border-t border-border px-4 py-5 pb-8 text-center text-sm text-muted sm:px-6"
    >
      <p>Discover opportunities in one place.</p>
      <p v-if="!auth.isAuthenticated" class="mt-2">
        <RouterLink to="/admin/login" class="text-sm text-muted">Staff sign in</RouterLink>
      </p>
    </footer>
  </div>
</template>
