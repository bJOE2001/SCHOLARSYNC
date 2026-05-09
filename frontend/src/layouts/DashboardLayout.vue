<script setup>
import { ref } from 'vue'
import AppSidebar from '../components/layout/AppSidebar.vue'
import Topbar from '../components/layout/Topbar.vue'

defineProps({
  sidebarTitle: {
    type: String,
    required: true,
  },
  sidebarSubtitle: {
    type: String,
    required: true,
  },
  sidebarItems: {
    type: Array,
    required: true,
  },
  userName: {
    type: String,
    required: true,
  },
  context: {
    type: String,
    required: true,
  },
  roleLabel: {
    type: String,
    required: true,
  },
  notificationCount: {
    type: Number,
    default: 0,
  },
})

const sidebarOpen = ref(false)
</script>

<template>
  <div class="min-h-screen bg-slate-50 lg:flex lg:items-start">
    <AppSidebar
      :title="sidebarTitle"
      :subtitle="sidebarSubtitle"
      :items="sidebarItems"
      :is-open="sidebarOpen"
      @close="sidebarOpen = false"
    />

    <div class="min-w-0 flex-1">
      <Topbar
        :user-name="userName"
        :context="context"
        :role-label="roleLabel"
        :notification-count="notificationCount"
        @toggle-sidebar="sidebarOpen = true"
      />

      <main class="w-full px-4 py-5 sm:px-5 lg:px-6 lg:py-6 xl:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>
