

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
    <section v-if="errorMessage" class="mb-4 rounded-md bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="successMessage" class="mb-4 rounded-md bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700">
      {{ successMessage }}
    </section>

    <form class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm" @submit.prevent="openSubmitConfirmation">
      <div class="border-b border-slate-200 p-4 sm:p-5">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">Scholarship Application</p>
        <div class="mt-1 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h2 class="text-xl font-bold text-slate-950">Application Form</h2>
            <p class="mt-1 text-sm text-slate-500">
              Complete each section in order. Locked sections will open after the previous requirements are filled.
            </p>
          </div>
          <p class="text-sm font-semibold text-slate-500">
            {{ application.scholarshipProgram || 'No scholarship selected' }}
          </p>
        </div>
      </div>

      <div v-if="loading" class="p-4 text-sm font-semibold text-slate-500">
        Loading application form...
      </div>

      <div v-else>
        <section class="space-y-4 p-4 lg:p-5">
          <section class="rounded-md border border-slate-200 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasProgram)">
                  <svg
                    v-if="hasProgram"
                    class="h-4 w-4"
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">Program Selection</p>
              </div>
              <span class="inline-flex rounded-md px-2.5 py-0.5 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[0].status)">
                {{ applicationSteps[0].status }}
              </span>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
              <FormInput
                id="scholarship-program"
                v-model="application.scholarshipProgram"
                label="Scholarship Program"
                :options="scholarshipOptions"
              />
            </div>
          </section>

          <section
            class="rounded-md border p-4 transition"
            :class="isStudentInfoLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isStudentInfoLocked" class="min-w-0">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasConfirmedInformation, isStudentInfoLocked)">
                    <svg
                      v-if="hasConfirmedInformation"
                      class="h-4 w-4"
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
                  <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="isStudentInfoLocked ? 'text-slate-400' : 'text-indigo-700'">Student Information</p>
                </div>
                <span class="inline-flex rounded-md px-2.5 py-0.5 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[1].status)">
                  {{ applicationSteps[1].status }}
                </span>
              </div>

              <div v-if="isStudentInfoLocked" class="mt-4 rounded-md border border-dashed border-slate-300 bg-white p-3 text-sm font-semibold text-slate-500">
                Select a scholarship program first to unlock this section.
              </div>

              <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="border-b border-slate-200 pb-3">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Student Name</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.name }}</p>
                </div>
                <div class="border-b border-slate-200 pb-3">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.email }}</p>
                </div>
                <div class="border-b border-slate-200 pb-3">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Program</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.program }}</p>
                </div>
                <div class="border-b border-slate-200 pb-3">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Phone</p>
                  <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.phone }}</p>
                </div>
              </div>

              <label class="mt-4 flex items-start gap-3 rounded-md bg-slate-50 p-3 text-sm font-semibold text-slate-700">
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
            class="rounded-md border p-4 transition"
            :class="isAcademicLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isAcademicLocked" class="min-w-0">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasAcademicDetails, isAcademicLocked)">
                    <svg
                      v-if="hasAcademicDetails"
                      class="h-4 w-4"
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
                  <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="isAcademicLocked ? 'text-slate-400' : 'text-indigo-700'">Academic and Personal Details</p>
                </div>
                <span class="inline-flex rounded-md px-2.5 py-0.5 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[2].status)">
                  {{ applicationSteps[2].status }}
                </span>
              </div>

              <div v-if="isAcademicLocked" class="mt-4 rounded-md border border-dashed border-slate-300 bg-white p-3 text-sm font-semibold text-slate-500">
                Confirm your student information first to unlock academic details.
              </div>

              <div class="mt-4 grid gap-4 md:grid-cols-2">
                <FormInput id="gpa" v-model="application.gpa" label="GPA" placeholder="Example: 1.45" />
                <FormInput id="year-level" v-model="application.yearLevel" label="Year Level" :options="yearLevelOptions" />
                <div class="md:col-span-2">
                  <FormInput id="address" v-model="application.address" label="Address" :placeholder="studentProfile.address" />
                </div>
              </div>
            </fieldset>
          </section>

          <section
            class="rounded-md border p-4 transition"
            :class="isReasonLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isReasonLocked" class="min-w-0">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasReason, isReasonLocked)">
                    <svg
                      v-if="hasReason"
                      class="h-4 w-4"
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
                  <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="isReasonLocked ? 'text-slate-400' : 'text-indigo-700'">Reason</p>
                </div>
                <span class="inline-flex rounded-md px-2.5 py-0.5 text-xs font-bold ring-1" :class="timelineBadgeClass(applicationSteps[3].status)">
                  {{ applicationSteps[3].status }}
                </span>
              </div>

              <div v-if="isReasonLocked" class="mt-4 rounded-md border border-dashed border-slate-300 bg-white p-3 text-sm font-semibold text-slate-500">
                Fill in GPA, year level, and address first to unlock the reason section.
              </div>

              <div class="mt-4">
                <FormInput
                  id="reason"
                  v-model="application.reason"
                  label="Reason"
                  placeholder="Briefly explain why you are applying for this scholarship."
                  textarea
                  :rows="4"
                />
              </div>

            </fieldset>
          </section>

          <section
            class="rounded-md border p-4 transition"
            :class="isFilesLocked ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white'"
          >
            <fieldset :disabled="isFilesLocked" class="min-w-0">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md text-sm font-black" :class="completionIconClass(hasSupportingDocuments, isFilesLocked)">
                    <svg
                      v-if="hasSupportingDocuments"
                      class="h-4 w-4"
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
                    <span v-else>5</span>
                  </span>
                  <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="isFilesLocked ? 'text-slate-400' : 'text-indigo-700'">Required Documents</p>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500"
                  :disabled="isFilesLocked"
                  @click="addSupportingDocument"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                  </svg>
                  Add file
                </button>
              </div>

              <div v-if="supportingDocuments.length" class="mt-4 space-y-3">
                <div
                  v-for="(document, index) in supportingDocuments"
                  :key="document.id"
                  class="grid max-w-3xl grid-cols-[minmax(0,1fr)_3rem] items-center gap-3"
                >
                  <label
                    :for="`supporting-document-file-${document.id}`"
                    class="flex h-12 min-w-0 flex-1 cursor-pointer items-center rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 transition hover:border-indigo-200 focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-100"
                  >
                    <span class="shrink-0 rounded-md bg-indigo-50 px-3 py-2 text-sm font-bold text-indigo-700">
                      Choose File
                    </span>
                    <span class="ml-3 min-w-0 truncate text-slate-500">
                      {{ document.file?.name || 'No file chosen' }}
                    </span>
                    <input
                      :id="`supporting-document-file-${document.id}`"
                      type="file"
                      class="sr-only"
                      :aria-label="`Upload required document ${index + 1}`"
                      @change="handleSupportingDocumentFileChange(document, $event)"
                    />
                  </label>
                  <button
                    type="button"
                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="isFilesLocked"
                    :aria-label="`Remove file ${index + 1}`"
                    @click="removeSupportingDocument(document.id)"
                  >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                    </svg>
                  </button>
                </div>
              </div>
            </fieldset>
          </section>
        </section>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-semibold text-slate-500">
          {{ isSubmitDisabled ? 'Complete all required sections to submit your application.' : 'All sections are complete. You can submit your application.' }}
        </p>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
          <button
            type="button"
            class="rounded-md border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
            @click="openReviewDialog"
          >
            Review
          </button>
          <button
            type="submit"
            class="rounded-md bg-indigo-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500"
            :disabled="isSubmitDisabled || submitting"
          >
            {{ submitting ? 'Submitting...' : 'Submit Application' }}
          </button>
        </div>
      </div>
    </form>
  </DashboardLayout>

  <Teleport to="body">
    <div
      v-if="showReviewDialog"
      class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/50 p-4"
      aria-modal="true"
      role="dialog"
      aria-labelledby="application-review-title"
      @click.self="closeReviewDialog"
    >
      <div class="w-full max-w-2xl rounded-md bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">Application Information</p>
            <h2 id="application-review-title" class="mt-1 text-lg font-bold text-slate-950">Review Application</h2>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
            aria-label="Close review dialog"
            @click="closeReviewDialog"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
            </svg>
          </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto p-5">
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Scholarship Program</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.scholarshipProgram || 'Not selected yet' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Student Name</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.name || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</p>
              <p class="mt-1 break-all text-sm font-semibold text-slate-950">{{ studentProfile.email || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Phone</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.phone || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Program</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ studentProfile.program || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.yearLevel || 'Not selected yet' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">GPA</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.gpa || 'Not entered yet' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Required Documents</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">
                {{ selectedSupportingDocuments.length }} file{{ selectedSupportingDocuments.length === 1 ? '' : 's' }}
              </p>
            </div>
            <div class="md:col-span-2">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Address</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.address || 'Not entered yet' }}</p>
            </div>
            <div class="md:col-span-2">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Reason</p>
              <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ application.reason || 'Not entered yet' }}</p>
            </div>
            <div class="md:col-span-2">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Files</p>
              <div v-if="selectedSupportingDocuments.length" class="mt-2 space-y-2">
                <p
                  v-for="(document, index) in selectedSupportingDocuments"
                  :key="`${document.id}-review`"
                  class="break-all rounded-md bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700"
                >
                  Required Document {{ index + 1 }}: {{ document.file.name }}
                </p>
              </div>
              <p v-else class="mt-1 text-sm font-semibold text-slate-950">No files selected yet</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-4">
          <button
            type="button"
            class="rounded-md bg-indigo-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800"
            @click="closeReviewDialog"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <ConfirmationDialog
    :show="showSubmitConfirmation"
    title="Submit this application?"
    :message="submitConfirmationMessage"
    confirm-label="Submit application"
    cancel-label="Review again"
    :loading="submitting"
    @confirm="submitApplication"
    @close="closeSubmitConfirmation"
  />
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import FormInput from '../../components/forms/FormInput.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const router = useRouter()
const route = useRoute()
const currentUser = getCurrentUser()
const loading = ref(true)
const submitting = ref(false)
const showSubmitConfirmation = ref(false)
const showReviewDialog = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const studentProfile = ref(currentUser ?? { name: 'Student' })
const scholarships = ref([])
const recentNotifications = ref([])
const supportingDocuments = ref([])
let supportingDocumentId = 0

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
const hasSupportingDocuments = computed(() => (
  supportingDocuments.value.length > 0 &&
  supportingDocuments.value.every((document) => document.file)
))
const selectedSupportingDocuments = computed(() => supportingDocuments.value.filter((document) => document.file))

const isStudentInfoLocked = computed(() => !hasProgram.value)
const isAcademicLocked = computed(() => !hasProgram.value || !hasConfirmedInformation.value)
const isReasonLocked = computed(() => isAcademicLocked.value || !hasAcademicDetails.value)
const isFilesLocked = computed(() => isReasonLocked.value || !hasReason.value)
const isSubmitDisabled = computed(() => (
  !hasProgram.value ||
  !hasConfirmedInformation.value ||
  !hasAcademicDetails.value ||
  !hasReason.value ||
  !hasSupportingDocuments.value
))
const submitConfirmationMessage = computed(() => {
  const fileCount = supportingDocuments.value.length
  const fileLabel = `${fileCount} file${fileCount === 1 ? '' : 's'}`

  return `Your application for ${application.scholarshipProgram || 'the selected scholarship'} with ${fileLabel} will be sent for review.`
})

const applicationSteps = computed(() => [
  {
    status: hasProgram.value ? 'Complete' : 'In Progress',
    locked: false,
  },
  {
    status: isStudentInfoLocked.value ? 'Locked' : hasConfirmedInformation.value ? 'Complete' : 'Available',
    locked: isStudentInfoLocked.value,
  },
  {
    status: isAcademicLocked.value ? 'Locked' : hasAcademicDetails.value ? 'Complete' : 'Available',
    locked: isAcademicLocked.value,
  },
  {
    status: isReasonLocked.value ? 'Locked' : hasReason.value ? 'Complete' : 'Available',
    locked: isReasonLocked.value,
  },
])

function createSupportingDocument() {
  supportingDocumentId += 1

  return {
    id: supportingDocumentId,
    file: null,
  }
}

function addSupportingDocument() {
  supportingDocuments.value.push(createSupportingDocument())
}

function removeSupportingDocument(documentId) {
  supportingDocuments.value = supportingDocuments.value.filter((document) => document.id !== documentId)
}

function handleSupportingDocumentFileChange(document, event) {
  const file = event.target.files?.[0] ?? null

  document.file = file
}

async function uploadSupportingDocuments(applicationId) {
  await Promise.all(supportingDocuments.value.map((document, index) => {
    const formData = new FormData()
    formData.append('documentType', `Required Document ${index + 1}`)
    formData.append('userId', currentUser?.id ?? '')
    formData.append('applicationId', applicationId)
    formData.append('file', document.file)

    return api.createDocument(formData)
  }))
}

function preselectProgramFromRoute() {
  const selectedProgram = typeof route.query.program === 'string' ? route.query.program : ''

  if (!selectedProgram) {
    return
  }

  const matchingScholarship = scholarships.value.find((scholarship) => (
    scholarship.status === 'Open' &&
    scholarship.scholarshipName === selectedProgram
  ))

  if (matchingScholarship) {
    application.scholarshipProgram = matchingScholarship.scholarshipName
  }
}

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

function openSubmitConfirmation() {
  if (isSubmitDisabled.value) {
    return
  }

  errorMessage.value = ''
  successMessage.value = ''
  showSubmitConfirmation.value = true
}

function closeSubmitConfirmation() {
  if (submitting.value) {
    return
  }

  showSubmitConfirmation.value = false
}

function openReviewDialog() {
  showReviewDialog.value = true
}

function closeReviewDialog() {
  showReviewDialog.value = false
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
    preselectProgramFromRoute()
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

  let createdApplication = null

  try {
    createdApplication = await api.createApplication({
      userId: currentUser?.id,
      scholarshipProgram: application.scholarshipProgram,
      gpa: application.gpa,
      yearLevel: application.yearLevel,
      address: application.address,
      reason: application.reason,
    })

    await uploadSupportingDocuments(createdApplication.id)

    successMessage.value = 'Application submitted successfully.'
    showSubmitConfirmation.value = false
    router.push('/student/status')
  } catch (error) {
    showSubmitConfirmation.value = false
    errorMessage.value = createdApplication
      ? `${error.message} Your application was created, but one or more files may not have uploaded. Please check Upload Documents.`
      : error.message
  } finally {
    submitting.value = false
  }
}

onMounted(loadFormData)
</script>