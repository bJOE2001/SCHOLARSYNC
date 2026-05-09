<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { api } from '../../services/api'

const props = defineProps({
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

const emit = defineEmits(['toggleSidebar'])
const router = useRouter()

const notificationsOpen = ref(false)
const profileMenuOpen = ref(false)

const notifications = computed(() => props.notificationItems ?? [])
const initials = computed(() => props.userName.split(' ').map((part) => part[0]).join('').slice(0, 2))
const profilePath = computed(() => props.roleLabel.toLowerCase().includes('applicant') ? '/student/profile' : '/admin/profile')

function openNotifications() {
  notificationsOpen.value = true
  profileMenuOpen.value = false
}

function closeNotifications() {
  notificationsOpen.value = false
}

function toggleProfileMenu() {
  profileMenuOpen.value = !profileMenuOpen.value
  notificationsOpen.value = false
}

function closeProfileMenu() {
  profileMenuOpen.value = false
}

async function logout() {
  closeProfileMenu()
  await api.logout()
  router.push('/login')
}

function handleEscape(event) {
  if (event.key === 'Escape') {
    notificationsOpen.value = false
    profileMenuOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <header class="sticky top-0 z-20 min-h-24 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur md:px-6">
    <div class="flex min-h-16 items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <button
          type="button"
          class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1 rounded-md border border-slate-200 text-slate-700 transition hover:bg-slate-50 lg:hidden"
          aria-label="Open navigation"
          @click="emit('toggleSidebar')"
        >
          <span class="block h-0.5 w-5 rounded-md bg-current"></span>
          <span class="block h-0.5 w-5 rounded-md bg-current"></span>
          <span class="block h-0.5 w-5 rounded-md bg-current"></span>
        </button>
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">{{ roleLabel }}</p>
          <h1 class="text-lg font-bold text-slate-950 md:text-2xl">{{ context }}</h1>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="relative inline-flex h-11 w-11 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
          aria-label="Notifications"
          :aria-expanded="notificationsOpen"
          @click="openNotifications"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
              d="M15 17H9m9-1.5c-.8-.8-1.2-1.7-1.2-2.8V10a4.8 4.8 0 0 0-9.6 0v2.7c0 1.1-.4 2-1.2 2.8L5 16.5h14l-1-1Zm-4 3a2 2 0 0 1-4 0"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <span
            v-if="notificationCount"
            class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-md bg-indigo-700 px-1 text-[11px] font-bold text-white"
          >
            {{ notificationCount }}
          </span>
        </button>

        <div class="hidden text-right sm:block">
          <p class="text-sm font-bold text-slate-950">{{ userName }}</p>
          <p class="text-xs text-slate-500">{{ roleLabel }}</p>
        </div>

        <div class="relative">
          <button
            type="button"
            class="grid h-11 w-11 place-items-center rounded-md bg-slate-900 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800"
            aria-label="Open profile menu"
            :aria-expanded="profileMenuOpen"
            @click="toggleProfileMenu"
          >
            {{ initials }}
          </button>

          <div
            v-if="profileMenuOpen"
            class="fixed inset-0 z-40"
            aria-hidden="true"
            @click="closeProfileMenu"
          ></div>

          <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-1 opacity-0"
          >
            <div
              v-if="profileMenuOpen"
              class="absolute right-0 z-50 mt-3 w-64 overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-950/10"
            >
              <div class="border-b border-slate-200 px-4 py-4">
                <p class="text-sm font-bold text-slate-950">{{ userName }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ roleLabel }}</p>
              </div>
              <div class="p-2">
                <RouterLink
                  :to="profilePath"
                  class="block rounded-md px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                  @click="closeProfileMenu"
                >
                  Profile
                </RouterLink>
                <button
                  type="button"
                  class="block w-full rounded-md px-4 py-3 text-left text-sm font-bold text-rose-700 transition hover:bg-rose-50"
                  @click="logout"
                >
                  Logout
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>

  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="notificationsOpen"
        class="fixed inset-0 z-50 bg-slate-950/30"
        aria-hidden="true"
        @click="closeNotifications"
      ></div>
    </Transition>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <aside
        v-if="notificationsOpen"
        class="fixed inset-y-0 right-0 z-[60] flex w-full max-w-sm flex-col border-l border-slate-200 bg-white shadow-2xl shadow-slate-950/20 sm:w-[380px]"
        aria-label="Notifications panel"
        aria-modal="true"
        role="dialog"
      >
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">Notifications</p>
            <h2 class="mt-1 text-lg font-bold text-slate-950">Recent updates</h2>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-50"
            aria-label="Close notifications"
            @click="closeNotifications"
          >
            <span class="relative h-5 w-5" aria-hidden="true">
              <span class="absolute left-1/2 top-1/2 h-0.5 w-5 -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-md bg-current"></span>
              <span class="absolute left-1/2 top-1/2 h-0.5 w-5 -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-md bg-current"></span>
            </span>
          </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto">
          <div class="divide-y divide-slate-200">
            <div
              v-for="notification in notifications"
              :key="notification.title"
              class="px-4 py-3 transition hover:bg-slate-50"
            >
              <div class="flex items-start gap-2.5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-md bg-indigo-700"></span>
                <div>
                  <h3 class="text-sm font-bold text-slate-950">{{ notification.title }}</h3>
                  <p class="mt-1 text-xs leading-5 text-slate-600">{{ notification.message }}</p>
                  <p class="mt-2 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ notification.time }}</p>
                </div>
              </div>
            </div>
            <p v-if="notifications.length === 0" class="px-4 py-6 text-sm font-semibold text-slate-500">
              No notifications yet.
            </p>
          </div>
        </div>

        <div class="border-t border-slate-200 p-4">
          <RouterLink
            :to="profilePath.includes('student') ? '/student/announcements' : '/admin/announcements'"
            class="inline-flex w-full justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-800"
            @click="closeNotifications"
          >
            View all notifications
          </RouterLink>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>
