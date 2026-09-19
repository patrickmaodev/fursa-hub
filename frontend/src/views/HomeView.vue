<script setup>
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import AppBadge from '@/components/AppBadge.vue'
import AppButton from '@/components/AppButton.vue'
import AppCard from '@/components/AppCard.vue'
import AppModal from '@/components/AppModal.vue'

const previewModalOpen = ref(false)
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl space-y-4">
      <AppBadge variant="featured">Kilimanjaro design system</AppBadge>
      <h1>Discover opportunities</h1>
      <p class="text-muted">
        Fursa Hub aggregates jobs, scholarships, internships, fellowships, grants, tenders,
        training, and more — with links to apply on the original source.
      </p>
      <p class="text-sm text-muted">Full listings arrive in Phase 2. Preview card layout below.</p>
    </div>

    <section class="mt-10">
      <h2 class="mb-4 text-lg">Listing preview</h2>
      <AppCard hoverable>
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-base">Software Developer</h3>
              <AppBadge variant="featured">Featured</AppBadge>
            </div>
            <p class="text-sm font-medium text-secondary">Vodacom Tanzania</p>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-3 text-sm text-muted">
          <span>Dar es Salaam</span>
          <span class="text-warning">Closes 30 Sep 2026</span>
        </div>
        <p class="mt-3 text-sm text-muted">
          Build and maintain customer-facing services. Experience with PHP or Java and REST APIs
          preferred.
        </p>
        <div class="mt-5 flex flex-wrap items-center gap-3">
          <AppButton size="sm">View opportunity</AppButton>
          <AppButton variant="outline" size="sm">Save</AppButton>
        </div>
      </AppCard>
    </section>

    <p class="mt-8">
      <AppButton variant="ghost" size="sm" type="button" @click="previewModalOpen = true">
        Open modal preview
      </AppButton>
    </p>

    <AppModal v-model="previewModalOpen" title="Design system" size="sm">
      <p class="text-sm text-muted">
        Shared components (<code class="text-foreground">AppButton</code>,
        <code class="text-foreground">AppCard</code>, <code class="text-foreground">AppBadge</code>)
        use Kilimanjaro tokens. Primary actions are teal; gold is for featured highlights.
      </p>
      <template #footer>
        <AppButton variant="outline" size="sm" type="button" @click="previewModalOpen = false">
          Cancel
        </AppButton>
        <AppButton size="sm" type="button" @click="previewModalOpen = false">Continue</AppButton>
      </template>
    </AppModal>
  </AppLayout>
</template>
