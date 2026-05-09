<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import AppSidebar from '../components/layout/AppSidebar.vue'
import Topbar from '../components/layout/Topbar.vue'
import { api, getCurrentUser } from '../services/api'

const props = defineProps({
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
  notificationItems: {
    type: Array,
    default: undefined,
  },
})

const sidebarOpen = ref(false)
const loadedNotifications = ref([])
let notificationTimer = null

const currentUser = getCurrentUser()
const notificationAudience = computed(() => props.roleLabel.toLowerCase().includes('applicant') ? 'student' : 'admin')
const effectiveNotifications = computed(() => props.notificationItems ?? loadedNotifications.value)
const effectiveNotificationCount = computed(() => effectiveNotifications.value.length)

async function loadNotifications() {
  if (props.notificationItems !== undefined) {
    return
  }

  try {
    loadedNotifications.value = await api.listNotifications({
      audience: notificationAudience.value,
      user_id: notificationAudience.value === 'student' ? currentUser?.id : undefined,
    })
  } catch {
    loadedNotifications.value = []
  }
}

onMounted(() => {
  loadNotifications()
  notificationTimer = window.setInterval(loadNotifications, 20000)
})

onBeforeUnmount(() => {
  if (notificationTimer) {
    window.clearInterval(notificationTimer)
  }
})
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
        :notification-count="effectiveNotificationCount"
        :notification-items="effectiveNotifications"
        @toggle-sidebar="sidebarOpen = true"
      />

      <main class="w-full px-4 py-5 sm:px-5 lg:px-6 lg:py-6 xl:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>
