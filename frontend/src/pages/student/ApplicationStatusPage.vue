<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const dashboard = ref({
  profile: currentUser ?? { name: 'Student' },
  currentApplication: null,
  recentNotifications: [],
})

const studentProfile = computed(() => dashboard.value.profile ?? currentUser ?? { name: 'Student' })
const currentApplication = computed(() => dashboard.value.currentApplication)
const recentNotifications = computed(() => dashboard.value.recentNotifications ?? [])

const applicationTimeline = computed(() => {
  const application = currentApplication.value

  if (!application) {
    return [
      {
        title: 'Application Submitted',
        date: 'Pending',
        status: 'Pending',
        detail: 'Submit an application to start tracking progress.',
      },
    ]
  }

  const documentsUploaded = application.documents?.length > 0
  const isFinal = ['Approved', 'Rejected'].includes(application.status)

  return [
    {
      title: 'Application Submitted',
      date: application.dateSubmitted,
      status: 'Completed',
      detail: 'Application form was submitted by the applicant.',
    },
    {
      title: 'Documents Uploaded',
      date: documentsUploaded ? application.documents[0].uploadDate : 'Pending',
      status: documentsUploaded ? 'Completed' : 'Pending',
      detail: documentsUploaded ? 'Submitted documents are available for review.' : 'Required documents are still pending.',
    },
    {
      title: 'Under Review',
      date: application.status === 'Pending' ? 'Pending' : application.dateSubmitted,
      status: isFinal ? 'Completed' : application.status === 'Pending' ? 'Pending' : 'Current',
      detail: 'Scholarship officer is validating eligibility and documentary requirements.',
    },
    {
      title: 'Approved / Rejected',
      date: isFinal ? application.dateSubmitted : 'Pending',
      status: isFinal ? application.status : 'Pending',
      detail: 'Final result will be posted after assessment.',
    },
  ]
})

async function loadStatus() {
  loading.value = true
  errorMessage.value = ''

  try {
    dashboard.value = await api.getStudentDashboard(currentUser?.id)
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadStatus)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Application Status"
    role-label="Applicant"
    :notification-count="recentNotifications.length"
    :notification-items="recentNotifications"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
      <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Current Application</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ currentApplication?.program || 'No application submitted' }}</h2>
          </div>
          <StatusBadge :status="currentApplication?.status || 'Pending'" />
        </div>

        <p v-if="loading" class="mt-8 rounded-md bg-slate-50 p-4 text-sm font-semibold text-slate-500">
          Loading status...
        </p>

        <div v-else class="mt-8 space-y-5">
          <div
            v-for="item in applicationTimeline"
            :key="item.title"
            class="relative rounded-md border border-slate-200 p-5"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 class="text-base font-bold text-slate-950">{{ item.title }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ item.detail }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ item.date }}</span>
                <StatusBadge :status="item.status" />
              </div>
            </div>
          </div>
        </div>
      </article>

      <aside class="space-y-6">
        <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Administrator Remarks</p>
          <p class="mt-4 text-sm leading-7 text-slate-600">
            {{ currentApplication?.remarks || 'No administrator remarks yet.' }}
          </p>
        </article>

        <article class="rounded-md border border-indigo-100 bg-indigo-50 p-6">
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Reminder</p>
          <h3 class="mt-3 text-lg font-bold text-slate-950">Complete pending requirements</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">
            Submit document revisions before May 20, 2026 to keep your application moving.
          </p>
        </article>
      </aside>
    </section>
  </DashboardLayout>
</template>
