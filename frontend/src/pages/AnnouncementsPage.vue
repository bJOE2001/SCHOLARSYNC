<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import FormInput from '../components/forms/FormInput.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import { adminNavigation, studentNavigation } from '../data/navigation'
import { api, getCurrentUser } from '../services/api'

const route = useRoute()
const isAdmin = computed(() => route.meta.role === 'admin')
const currentUser = getCurrentUser()
const announcements = ref([])
const recentNotifications = ref([])
const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const studentProfile = ref(currentUser ?? { name: 'Student' })

const form = reactive({
  title: '',
  message: '',
  audience: '',
  date: '',
})

const audienceOptions = [
  { label: 'All Students', value: 'All Students' },
  { label: 'Applicants', value: 'Applicants' },
  { label: 'Approved Scholars', value: 'Approved Scholars' },
  { label: 'At-Risk Scholars', value: 'At-Risk Scholars' },
]

const layoutConfig = computed(() => {
  if (isAdmin.value) {
    const user = currentUser ?? {}

    return {
      sidebarTitle: 'ScholarSync',
      sidebarSubtitle: 'Officer Portal',
      sidebarItems: adminNavigation,
      userName: user.name || 'Scholarship Officer',
      context: 'Announcements',
      roleLabel: 'Scholarship Officer',
      notificationCount: announcements.value.length,
    }
  }

  return {
    sidebarTitle: 'ScholarSync',
    sidebarSubtitle: 'Applicant Portal',
    sidebarItems: studentNavigation,
    userName: studentProfile.value.name,
    context: 'Notifications',
    roleLabel: 'Applicant',
    notificationCount: recentNotifications.value.length,
  }
})

async function loadAnnouncements() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [announcementList, dashboard] = await Promise.all([
      api.listAnnouncements(),
      isAdmin.value ? Promise.resolve(null) : api.getStudentDashboard(currentUser?.id).catch(() => null),
    ])

    announcements.value = announcementList
    studentProfile.value = dashboard?.profile ?? studentProfile.value
    recentNotifications.value = dashboard?.recentNotifications ?? []
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function publishAnnouncement() {
  submitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.createAnnouncement(form)
    successMessage.value = 'Announcement published successfully.'
    Object.assign(form, {
      title: '',
      message: '',
      audience: '',
      date: '',
    })
    await loadAnnouncements()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    submitting.value = false
  }
}

onMounted(loadAnnouncements)
</script>

<template>
  <DashboardLayout
    :sidebar-title="layoutConfig.sidebarTitle"
    :sidebar-subtitle="layoutConfig.sidebarSubtitle"
    :sidebar-items="layoutConfig.sidebarItems"
    :user-name="layoutConfig.userName"
    :context="layoutConfig.context"
    :role-label="layoutConfig.roleLabel"
    :notification-count="layoutConfig.notificationCount"
    :notification-items="isAdmin ? undefined : recentNotifications"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="successMessage" class="mb-5 rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
      {{ successMessage }}
    </section>

    <section v-if="isAdmin" class="mb-6 rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Create Announcement</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Publish an update</h2>

      <form class="mt-6 grid gap-5 md:grid-cols-2" @submit.prevent="publishAnnouncement">
        <FormInput id="announcement-title" v-model="form.title" label="Title" placeholder="Scholarship renewal window" />
        <FormInput id="announcement-audience" v-model="form.audience" label="Audience" :options="audienceOptions" />
        <FormInput id="announcement-date" v-model="form.date" label="Date" type="date" />
        <div class="md:col-span-2">
          <FormInput
            id="announcement-message"
            v-model="form.message"
            label="Message"
            placeholder="Write announcement details here."
            textarea
            :rows="5"
          />
        </div>
        <div class="md:col-span-2">
          <button type="submit" class="rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300" :disabled="submitting">
            {{ submitting ? 'Publishing...' : 'Publish Announcement' }}
          </button>
        </div>
      </form>
    </section>

    <section>
      <div class="mb-4">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">
          {{ isAdmin ? 'Announcement List' : 'Latest Announcements' }}
        </p>
        <h2 class="mt-2 text-xl font-bold text-slate-950">
          {{ isAdmin ? 'Published updates' : 'Scholarship office updates' }}
        </h2>
      </div>

      <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
        <p v-if="loading" class="p-5 text-sm font-semibold text-slate-500">
          Loading announcements...
        </p>
        <template v-else>
          <div
            v-for="announcement in announcements"
            :key="announcement.id || announcement.title"
            class="border-b border-slate-200 p-5 last:border-b-0 hover:bg-slate-50"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 class="text-lg font-bold text-slate-950">{{ announcement.title }}</h3>
                <p class="mt-2 text-sm font-semibold text-slate-500">{{ announcement.date }}</p>
              </div>
              <StatusBadge :status="announcement.audience" />
            </div>
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ announcement.message }}</p>
          </div>
        </template>
        <p v-if="!loading && !announcements.length" class="p-5 text-sm font-semibold text-slate-500">
          No announcements found.
        </p>
      </div>
    </section>
  </DashboardLayout>
</template>
