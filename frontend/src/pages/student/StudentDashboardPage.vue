<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import StatCard from '../../components/ui/StatCard.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const scholarships = ref([])
const selectedScholarship = ref(null)
const dashboard = ref({
  profile: currentUser ?? { name: 'Student' },
  stats: [],
  currentApplication: null,
  progressSteps: ['Submitted', 'Under Review', 'Approved / Rejected'],
  recentNotifications: [],
})

const studentProfile = computed(() => dashboard.value.profile ?? currentUser ?? { name: 'Student' })
const studentDashboardStats = computed(() => dashboard.value.stats ?? [])
const recentNotifications = computed(() => dashboard.value.recentNotifications ?? [])
const availableScholarships = computed(() => scholarships.value.filter((scholarship) => scholarship.status === 'Open'))

function applicationLink(scholarship) {
  return {
    path: '/student/application',
    query: {
      program: scholarship.scholarshipName,
    },
  }
}

function openDetails(scholarship) {
  selectedScholarship.value = scholarship
}

function closeDetails() {
  selectedScholarship.value = null
}

async function loadDashboard() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [dashboardData, scholarshipList] = await Promise.all([
      api.getStudentDashboard(currentUser?.id),
      api.listScholarships({ status: 'Open' }),
    ])

    dashboard.value = dashboardData
    scholarships.value = scholarshipList
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Student Dashboard"
    role-label="Applicant"
    :notification-count="recentNotifications.length"
    :notification-items="recentNotifications"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading dashboard...
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <StatCard
        v-for="stat in studentDashboardStats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :tone="stat.tone"
      />
    </section>

    <section class="mt-6 rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Available Scholarship Programs</p>
        </div>
        <RouterLink to="/student/application" class="inline-flex justify-center rounded-md border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
          Open Application Form
        </RouterLink>
      </div>

      <div v-if="availableScholarships.length" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="scholarship in availableScholarships"
          :key="scholarship.id"
          class="flex min-h-[280px] flex-col rounded-md border border-slate-200 p-5"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">{{ scholarship.scholarshipType || 'Scholarship Program' }}</p>
              <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-6 text-slate-950">{{ scholarship.scholarshipName }}</h3>
            </div>
            <StatusBadge :status="scholarship.status" />
          </div>

          <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">
            {{ scholarship.description || scholarship.announcementDetails || 'Program details are available from the scholarship office.' }}
          </p>

          <dl class="mt-4 grid gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2">
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Deadline</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.deadline || 'To be announced' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Slots</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.availableSlots || '0' }}</dd>
            </div>
          </dl>

          <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row">
            <RouterLink :to="applicationLink(scholarship)" class="inline-flex w-full justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-800">
              Apply
            </RouterLink>
            <button type="button" class="inline-flex w-full justify-center rounded-md border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="openDetails(scholarship)">
              View Details
            </button>
          </div>
        </article>
      </div>

      <p v-else class="mt-6 rounded-md bg-slate-50 p-4 text-sm font-semibold text-slate-500">
        No open scholarship programs at the moment.
      </p>
    </section>
  </DashboardLayout>

  <Teleport to="body">
    <div
      v-if="selectedScholarship"
      class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4"
      aria-modal="true"
      role="dialog"
      aria-labelledby="student-scholarship-details-title"
      @click.self="closeDetails"
    >
      <section class="w-full max-w-3xl overflow-hidden rounded-md bg-white shadow-xl">
        <header class="flex items-start justify-between gap-4 border-b border-slate-200 p-6">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">{{ selectedScholarship.scholarshipType || 'Scholarship Program' }}</p>
            <h2 id="student-scholarship-details-title" class="mt-2 text-2xl font-bold text-slate-950">{{ selectedScholarship.scholarshipName }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ selectedScholarship.academicYear || 'Academic year not specified' }} · {{ selectedScholarship.semester || 'Semester not specified' }}</p>
          </div>
          <button type="button" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeDetails">
            Close
          </button>
        </header>

        <div class="max-h-[72vh] overflow-y-auto p-6">
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-md bg-slate-50 p-4">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status</p>
              <div class="mt-2"><StatusBadge :status="selectedScholarship.status" /></div>
            </div>
            <div class="rounded-md bg-slate-50 p-4">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Deadline</p>
              <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.deadline || 'To be announced' }}</p>
            </div>
            <div class="rounded-md bg-slate-50 p-4">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Slots</p>
              <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.availableSlots || '0' }}</p>
            </div>
            <div class="rounded-md bg-slate-50 p-4">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Minimum GPA</p>
              <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.minimumGpa || 'Not specified' }}</p>
            </div>
          </div>

          <div class="mt-6 space-y-6">
            <section>
              <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Program Description</h3>
              <p class="mt-3 text-sm leading-7 text-slate-700">{{ selectedScholarship.description || 'No description provided.' }}</p>
            </section>
            <section>
              <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Eligibility Requirements</h3>
              <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ selectedScholarship.eligibilityRequirements || 'No eligibility requirements provided.' }}</p>
            </section>
            <section>
              <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Required Documents</h3>
              <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ selectedScholarship.requiredDocuments || 'No required documents provided.' }}</p>
            </section>
            <section>
              <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Allowed Students</h3>
              <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ selectedScholarship.yearLevelAllowed || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Program/Course</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ selectedScholarship.programAllowed || 'Not specified' }}</dd>
                </div>
              </dl>
            </section>
            <section>
              <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Contact Person</h3>
              <p class="mt-3 text-sm leading-7 text-slate-700">{{ selectedScholarship.contactPerson || 'Scholarship Office' }}</p>
            </section>
          </div>
        </div>

        <footer class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 sm:flex-row sm:justify-end">
          <button type="button" class="rounded-md border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="closeDetails">
            Close
          </button>
          <RouterLink :to="applicationLink(selectedScholarship)" class="inline-flex justify-center rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
            Apply for this Program
          </RouterLink>
        </footer>
      </section>
    </div>
  </Teleport>
</template>
