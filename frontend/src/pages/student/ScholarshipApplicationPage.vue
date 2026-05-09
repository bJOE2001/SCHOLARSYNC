<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import FormInput from '../../components/forms/FormInput.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const router = useRouter()
const currentUser = getCurrentUser()
const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const studentProfile = ref(currentUser ?? { name: 'Student' })
const scholarships = ref([])
const recentNotifications = ref([])

const application = reactive({
  scholarshipProgram: '',
  informationConfirmed: false,
  gpa: '',
  yearLevel: '',
  address: '',
  reason: '',
})

const scholarshipOptions = computed(() => scholarships.value
  .filter((scholarship) => scholarship.status === 'Open')
  .map((scholarship) => ({
    label: scholarship.scholarshipName,
    value: scholarship.scholarshipName,
  })))

const yearLevelOptions = [
  { label: '1st Year', value: '1st Year' },
  { label: '2nd Year', value: '2nd Year' },
  { label: '3rd Year', value: '3rd Year' },
  { label: '4th Year', value: '4th Year' },
]

const hasProgram = computed(() => Boolean(application.scholarshipProgram))
const hasConfirmedInformation = computed(() => application.informationConfirmed)
const hasAcademicDetails = computed(() => Boolean(application.gpa && application.yearLevel && application.address))
const hasReason = computed(() => application.reason.trim().length > 0)

const isStudentInfoLocked = computed(() => !hasProgram.value)
const isAcademicLocked = computed(() => !hasProgram.value || !hasConfirmedInformation.value)
const isReasonLocked = computed(() => isAcademicLocked.value || !hasAcademicDetails.value)
const isSubmitDisabled = computed(() => !hasProgram.value || !hasConfirmedInformation.value || !hasAcademicDetails.value || !hasReason.value)

const applicationSteps = computed(() => [
  {
    title: 'Program Selection',
    subtitle: hasProgram.value ? application.scholarshipProgram : 'Choose scholarship program',
    status: hasProgram.value ? 'Complete' : 'In Progress',
    locked: false,
  },
  {
    title: 'Student Information',
    subtitle: hasConfirmedInformation.value ? 'Information confirmed' : 'Review applicant profile',
    status: isStudentInfoLocked.value ? 'Locked' : hasConfirmedInformation.value ? 'Complete' : 'Available',
    locked: isStudentInfoLocked.value,
  },
  {
    title: 'Academic Details',
    subtitle: hasAcademicDetails.value ? 'Academic details entered' : 'Enter GPA, year level, and address',
    status: isAcademicLocked.value ? 'Locked' : hasAcademicDetails.value ? 'Complete' : 'Available',
    locked: isAcademicLocked.value,
  },
  {
    title: 'Reason for Applying',
    subtitle: hasReason.value ? 'Statement completed' : 'Write application reason',
    status: isReasonLocked.value ? 'Locked' : hasReason.value ? 'Complete' : 'Available',
    locked: isReasonLocked.value,
  },
])

function timelineBadgeClass(status) {
  if (status === 'Complete') {
    return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  }

  if (status === 'Locked') {
    return 'bg-slate-100 text-slate-500 ring-slate-200'
  }

  return 'bg-indigo-50 text-indigo-700 ring-indigo-200'
}

function stepCircleClass(status, locked) {
  if (status === 'Complete') {
    return 'bg-emerald-600 text-white'
  }

  if (locked) {
    return 'bg-slate-200 text-slate-500'
  }

  return 'bg-indigo-700 text-white'
}

function completionIconClass(isComplete, isLocked = false) {
  if (isComplete) {
    return 'bg-emerald-600 text-white'
  }

  if (isLocked) {
    return 'bg-slate-200 text-slate-500'
  }

  return 'bg-indigo-700 text-white'
}

async function loadFormData() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [profile, scholarshipList, dashboard] = await Promise.all([
      api.getStudentProfile(currentUser?.id),
      api.listScholarships(),
      api.getStudentDashboard(currentUser?.id).catch(() => null),
    ])

    studentProfile.value = profile
    scholarships.value = scholarshipList
    recentNotifications.value = dashboard?.recentNotifications ?? []
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function submitApplication() {
  if (isSubmitDisabled.value) {
    return
  }

  submitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.createApplication({
      userId: currentUser?.id,
      scholarshipProgram: application.scholarshipProgram,
      gpa: application.gpa,
      yearLevel: application.yearLevel,
      address: application.address,
      reason: application.reason,
    })

    successMessage.value = 'Application submitted successfully.'
    router.push('/student/status')
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    submitting.value = false
  }
}

onMounted(loadFormData)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Scholarship Application"
    role-label="Applicant"
    :notification-count="recentNotifications.length"
    :notification-items="recentNotifications"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="successMessage" class="mb-5 rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
      {{ successMessage }}
    </section>

    <form class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm" @submit.prevent="submitApplication">
      <div class="border-b border-slate-200 p-5 sm:p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Scholarship Application</p>
        <div class="mt-2 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-slate-950">Application Form</h2>
            <p class="mt-1 text-sm text-slate-500">
              Complete each section in order. Locked sections will open after the previous requirements are filled.
            </p>
          </div>
          <p class="text-sm font-semibold text-slate-500">
            {{ application.scholarshipProgram || 'No scholarship selected' }}
          </p>
        </div>
      </div>

      <div v-if="loading" class="p-6 text-sm font-semibold text-slate-500">
        Loading application form...
      </div>

      <div v-else>
        <section class="space-y-6 p-5 sm:p-6 lg:p-8">
          <section class="rounded-md border border-slate-200 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="flex items-start gap-4">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasProgram)">
                  <svg
                    v-if="hasProgram"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                  >
                    <path
                      d="m5 12 4 4L19 6"
                      stroke="currentColor"
                      stroke-width="2.2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                  <span v-else>1</span>
                </span>
                <div>
                  <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Program Selection</p>
                  <h3 class="mt-2 flex flex-wrap items-center gap-2 text-xl font-bold text-slate-950">
                    Choose scholarship program
                    <span v-if="hasProgram" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      Checked
                    </span>
                  </h3>
                  <p class="mt-2 text-sm leading-6 text-slate-500">
                    Select the scholarship program where you want to submit this application.
                  </p>
                </div>
              </div>
              <span class="inline-flex rounded-md px-3 py-1 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[0].status)">
                {{ applicationSteps[0].status }}
              </span>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
              <FormInput
                id="scholarship-program"
                v-model="application.scholarshipProgram"
                label="Scholarship Program"
                :options="scholarshipOptions"
              />
            </div>
          </section>

          <section
            class="rounded-md border p-5 transition"
            :class="isStudentInfoLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isStudentInfoLocked" class="min-w-0">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                  <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasConfirmedInformation, isStudentInfoLocked)">
                    <svg
                      v-if="hasConfirmedInformation"
                      class="h-5 w-5"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <path
                        d="m5 12 4 4L19 6"
                        stroke="currentColor"
                        stroke-width="2.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span v-else>2</span>
                  </span>
                  <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em]" :class="isStudentInfoLocked ? 'text-slate-400' : 'text-indigo-700'">Student Information</p>
                    <h3 class="mt-2 flex flex-wrap items-center gap-2 text-xl font-bold text-slate-950">
                      Review applicant summary
                      <span v-if="hasConfirmedInformation" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Checked
                      </span>
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                      This section unlocks after selecting a scholarship program.
                    </p>
                  </div>
                </div>
                <span class="inline-flex rounded-md px-3 py-1 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[1].status)">
                  {{ applicationSteps[1].status }}
                </span>
              </div>

              <div v-if="isStudentInfoLocked" class="mt-5 rounded-md border border-dashed border-slate-300 bg-white p-4 text-sm font-semibold text-slate-500">
                Select a scholarship program first to unlock this section.
              </div>

              <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="border-b border-slate-200 pb-4">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Student Name</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.name }}</p>
                </div>
                <div class="border-b border-slate-200 pb-4">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.email }}</p>
                </div>
                <div class="border-b border-slate-200 pb-4">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Program</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.program }}</p>
                </div>
                <div class="border-b border-slate-200 pb-4">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Phone</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.phone }}</p>
                </div>
              </div>

              <label class="mt-5 flex items-start gap-3 rounded-md bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                <input
                  v-model="application.informationConfirmed"
                  type="checkbox"
                  class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-700 focus:ring-indigo-600"
                />
                <span>I confirm that my student information is correct.</span>
              </label>
            </fieldset>
          </section>

          <section
            class="rounded-md border p-5 transition"
            :class="isAcademicLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isAcademicLocked" class="min-w-0">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                  <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasAcademicDetails, isAcademicLocked)">
                    <svg
                      v-if="hasAcademicDetails"
                      class="h-5 w-5"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <path
                        d="m5 12 4 4L19 6"
                        stroke="currentColor"
                        stroke-width="2.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span v-else>3</span>
                  </span>
                  <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em]" :class="isAcademicLocked ? 'text-slate-400' : 'text-indigo-700'">Academic and Personal Details</p>
                    <h3 class="mt-2 flex flex-wrap items-center gap-2 text-xl font-bold text-slate-950">
                      Enter academic information
                      <span v-if="hasAcademicDetails" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Checked
                      </span>
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                      This section unlocks after confirming your student information.
                    </p>
                  </div>
                </div>
                <span class="inline-flex rounded-md px-3 py-1 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[2].status)">
                  {{ applicationSteps[2].status }}
                </span>
              </div>

              <div v-if="isAcademicLocked" class="mt-5 rounded-md border border-dashed border-slate-300 bg-white p-4 text-sm font-semibold text-slate-500">
                Confirm your student information first to unlock academic details.
              </div>

              <div class="mt-6 grid gap-5 md:grid-cols-2">
                <FormInput id="gpa" v-model="application.gpa" label="GPA" placeholder="Example: 1.45" />
                <FormInput id="year-level" v-model="application.yearLevel" label="Year Level" :options="yearLevelOptions" />
                <div class="md:col-span-2">
                  <FormInput id="address" v-model="application.address" label="Address" :placeholder="studentProfile.address" />
                </div>
              </div>
            </fieldset>
          </section>

          <section
            class="rounded-md border p-5 transition"
            :class="isReasonLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isReasonLocked" class="min-w-0">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                  <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasReason, isReasonLocked)">
                    <svg
                      v-if="hasReason"
                      class="h-5 w-5"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <path
                        d="m5 12 4 4L19 6"
                        stroke="currentColor"
                        stroke-width="2.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span v-else>4</span>
                  </span>
                  <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em]" :class="isReasonLocked ? 'text-slate-400' : 'text-indigo-700'">Reason and Review</p>
                    <h3 class="mt-2 flex flex-wrap items-center gap-2 text-xl font-bold text-slate-950">
                      Complete your application statement
                      <span v-if="hasReason" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Checked
                      </span>
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                      This section unlocks after completing the academic details.
                    </p>
                  </div>
                </div>
                <span class="inline-flex rounded-md px-3 py-1 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[3].status)">
                  {{ applicationSteps[3].status }}
                </span>
              </div>

              <div v-if="isReasonLocked" class="mt-5 rounded-md border border-dashed border-slate-300 bg-white p-4 text-sm font-semibold text-slate-500">
                Fill in GPA, year level, and address first to unlock the reason section.
              </div>

              <div class="mt-6">
                <FormInput
                  id="reason"
                  v-model="application.reason"
                  label="Reason for Applying"
                  placeholder="Briefly explain why you are applying for this scholarship."
                  textarea
                  :rows="6"
                />
              </div>

              <div class="mt-8 grid gap-4 border-t border-slate-200 pt-6 md:grid-cols-2">
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Scholarship Program</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.scholarshipProgram || 'Not selected yet' }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Student Name</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.name }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">GPA</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.gpa || 'Not entered yet' }}</p>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.yearLevel || 'Not selected yet' }}</p>
                </div>
              </div>
            </fieldset>
          </section>
        </section>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <p class="text-sm font-semibold text-slate-500">
          {{ isSubmitDisabled ? 'Complete all required sections to submit your application.' : 'All sections are complete. You can submit your application.' }}
        </p>
        <button
          type="submit"
          class="rounded-md bg-indigo-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500"
          :disabled="isSubmitDisabled || submitting"
        >
          {{ submitting ? 'Submitting...' : 'Submit Application' }}
        </button>
      </div>
    </form>
  </DashboardLayout>
</template>
