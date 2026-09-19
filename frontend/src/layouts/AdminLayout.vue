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
      <RouterLink
        to="/admin/dashboard"
        class="text-lg font-bold text-foreground no-underline hover:no-underline"
      >
        Fursa Admin
      </RouterLink>
      <nav class="flex flex-wrap items-center gap-3 text-sm font-medium sm:gap-4">
        <RouterLink
          to="/admin/dashboard"
          class="text-muted hover:text-foreground"
          active-class="!text-foreground"
        >
          Dashboard
        </RouterLink>
        <RouterLink to="/" class="text-muted hover:text-foreground">Public site</RouterLink>
        <span class="hidden text-muted sm:inline">{{ auth.user?.name }}</span>
        <AppButton variant="ghost" size="sm" type="button" @click="handleLogout">Log out</AppButton>
      </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-10">
      <slot />
    </main>
  </div>
</template>
