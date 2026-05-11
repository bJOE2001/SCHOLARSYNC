<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const uploadMessage = ref('')
const uploadingRequirementKey = ref('')
const profile = ref(currentUser ?? { name: 'Student' })
const applications = ref([])
const scholarships = ref([])
const uploadDocuments = ref([])
const recentNotifications = ref([])
const pendingUpload = ref(null)
const expandedApplicationId = ref('')

const studentProfile = computed(() => profile.value)
const requirementGroups = computed(() => applications.value
  .map((application) => {
    const scholarship = findScholarshipForApplication(application)
    const requiredDocuments = parseRequiredDocuments(scholarship?.requiredDocuments)

    if (!requiredDocuments.length) {
      return null
    }

    const requirementItems = buildRequirementItems(
      application,
      requiredDocuments,
      documentsForApplication(application),
    )
    const pendingCount = requirementItems.filter(needsRequirementUpload).length

    return {
      application,
      scholarship,
      title: scholarship?.scholarshipName ?? application.program,
      type: scholarship?.scholarshipType ?? 'Scholarship Program',
      description: scholarship?.description || scholarship?.announcementDetails || 'Document requirements are listed for this scholarship application.',
      datePosted: scholarship?.datePosted ?? 'Not posted',
      deadline: scholarship?.deadline ?? 'To be announced',
      requiredCount: requirementItems.length,
      completedCount: requirementItems.length - pendingCount,
      pendingCount,
      done: pendingCount === 0,
      requirementItems,
    }
  })
  .filter(Boolean))
const emptyRequirementsMessage = computed(() => applications.value.length
  ? 'Your submitted applications do not have document requirements listed yet.'
  : 'Submit a scholarship application before uploading requirements.')
const uploadConfirmationMessage = computed(() => {
  if (!pendingUpload.value) {
    return ''
  }

  return `${pendingUpload.value.file.name} will be uploaded as ${pendingUpload.value.documentType} for ${pendingUpload.value.scholarshipName}.`
})

function parseRequiredDocuments(value) {
  return String(value ?? '')
    .split(/\r?\n|,|;/)
    .map((document) => document
      .replace(/^\s*[-*\u2022]\s+/, '')
      .replace(/^\s*\d+[.)]\s+/, '')
      .trim())
    .filter(Boolean)
}

function normalizeRequirement(value) {
  return String(value ?? '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, ' ')
    .trim()
}

function genericDocumentIndex(document) {
  const match = String(document?.documentType ?? '').match(/required document\s+(\d+)/i)

  return match ? Number(match[1]) : Number.MAX_SAFE_INTEGER
}

function sortDocumentsByRequirementOrder(first, second) {
  const firstIndex = genericDocumentIndex(first)
  const secondIndex = genericDocumentIndex(second)

  if (firstIndex !== secondIndex) {
    return firstIndex - secondIndex
  }

  return String(second.uploadDateIso ?? '').localeCompare(String(first.uploadDateIso ?? ''))
}

function findScholarshipForApplication(application) {
  if (application.scholarshipId) {
    const matchingById = scholarships.value.find((scholarship) => scholarship.id === application.scholarshipId)

    if (matchingById) {
      return matchingById
    }
  }

  return scholarships.value.find((scholarship) => scholarship.scholarshipName === application.program) ?? null
}

function documentsForApplication(application) {
  const keyedDocuments = new Map()
  const applicationDocuments = application.documents ?? []
  const listedDocuments = uploadDocuments.value.filter((document) => document.applicationId === application.id)
  const documentSources = [...applicationDocuments, ...listedDocuments]

  documentSources.forEach((document, index) => {
    const key = document.id || `${document.applicationId}-${document.documentType}-${document.fileName}-${index}`

    keyedDocuments.set(key, {
      ...document,
      status: document.verificationStatus ?? document.status ?? 'Uploaded',
    })
  })

  return Array.from(keyedDocuments.values()).sort(sortDocumentsByRequirementOrder)
}

function buildRequirementItems(application, requiredDocuments, documents) {
  const remainingDocuments = [...documents]

  return requiredDocuments.map((documentType, index) => {
    const normalizedDocumentType = normalizeRequirement(documentType)
    let documentIndex = remainingDocuments.findIndex((document) => (
      normalizeRequirement(document.documentType) === normalizedDocumentType
    ))

    if (documentIndex === -1) {
      documentIndex = remainingDocuments.findIndex((document) => (
        genericDocumentIndex(document) === index + 1
      ))
    }

    const document = documentIndex >= 0 ? remainingDocuments.splice(documentIndex, 1)[0] : null

    return {
      id: document?.id ?? '',
      applicationId: application.id,
      documentType,
      fileName: document?.fileName ?? '',
      status: document?.status ?? 'Pending',
      document,
    }
  })
}

function requirementUploadKey(requirement) {
  return `${requirement.applicationId}:${normalizeRequirement(requirement.documentType)}`
}

function needsRequirementUpload(requirement) {
  return !requirement.document || !requirement.fileName || normalizeRequirement(requirement.status) === 'for revision'
}

function requirementGroupBadgeClass(requirementGroup) {
  return requirementGroup.done
    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
    : 'bg-amber-50 text-amber-700 ring-amber-200'
}

function toggleRequirements(applicationId) {
  expandedApplicationId.value = expandedApplicationId.value === applicationId ? '' : applicationId
}

function handleRequirementFileChange(requirementGroup, requirement, event) {
  const input = event.target
  const file = input.files?.[0]

  if (file) {
    requestDocumentUpload({
      applicationId: requirement.applicationId,
      documentType: requirement.documentType,
      file,
      scholarshipName: requirementGroup.title,
    })
  }

  input.value = ''
}

function requestDocumentUpload(upload) {
  uploadMessage.value = ''
  errorMessage.value = ''
  pendingUpload.value = upload
}

function closeUploadConfirmation() {
  if (uploadingRequirementKey.value) {
    return
  }

  pendingUpload.value = null
}

async function loadDocuments() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [dashboard, applicationList, scholarshipList, documents] = await Promise.all([
      api.getStudentDashboard(currentUser?.id),
      api.listApplications({ user_id: currentUser?.id }),
      api.listScholarships(),
      api.listDocuments({ user_id: currentUser?.id }),
    ])

    profile.value = dashboard.profile
    applications.value = applicationList
    scholarships.value = scholarshipList
    recentNotifications.value = dashboard.recentNotifications ?? []
    uploadDocuments.value = documents.map((document) => ({
      ...document,
      status: document.verificationStatus,
    }))
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function uploadDocument({ applicationId, documentType, file }) {
  uploadMessage.value = ''
  errorMessage.value = ''
  uploadingRequirementKey.value = requirementUploadKey({ applicationId, documentType })

  try {
    const formData = new FormData()
    formData.append('documentType', documentType)
    formData.append('userId', currentUser?.id ?? '')
    formData.append('applicationId', applicationId)
    formData.append('file', file)

    const document = await api.createDocument(formData)
    uploadMessage.value = `${document.documentType} uploaded successfully.`
    pendingUpload.value = null
    expandedApplicationId.value = applicationId
    await loadDocuments()
  } catch (error) {
    pendingUpload.value = null
    errorMessage.value = error.message
  } finally {
    uploadingRequirementKey.value = ''
  }
}

function confirmDocumentUpload() {
  if (!pendingUpload.value) {
    return
  }

  uploadDocument(pendingUpload.value)
}

onMounted(loadDocuments)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Upload Documents"
    role-label="Applicant"
    :notification-count="recentNotifications.length"
    :notification-items="recentNotifications"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="uploadMessage" class="mb-5 rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
      {{ uploadMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Scholarship Requirements</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Upload required documents by scholarship</h2>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
        Review each submitted scholarship application with listed document requirements. Completed applications are marked done.
      </p>
    </section>

    <section v-if="loading" class="mt-6 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading documents...
    </section>

    <section v-else-if="requirementGroups.length" class="mt-6 space-y-4">
      <article
        v-for="requirementGroup in requirementGroups"
        :key="requirementGroup.application.id"
        class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm"
      >
        <div class="p-5">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">{{ requirementGroup.type }}</p>
              <h3 class="mt-2 text-lg font-bold text-slate-950">{{ requirementGroup.title }}</h3>
              <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ requirementGroup.description }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <StatusBadge :status="requirementGroup.application.status" />
              <span
                class="inline-flex items-center rounded-md px-3 py-1 text-xs font-bold ring-1"
                :class="requirementGroupBadgeClass(requirementGroup)"
              >
                {{ requirementGroup.done ? 'Done' : `${requirementGroup.pendingCount} pending` }}
              </span>
            </div>
          </div>

          <dl class="mt-5 grid gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Requirements</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ requirementGroup.requiredCount }}</dd>
            </div>
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Uploaded</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ requirementGroup.completedCount }}</dd>
            </div>
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Date Posted</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ requirementGroup.datePosted }}</dd>
            </div>
            <div>
              <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Deadline</dt>
              <dd class="mt-1 text-sm font-semibold text-slate-950">{{ requirementGroup.deadline }}</dd>
            </div>
          </dl>

          <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold" :class="requirementGroup.done ? 'text-emerald-700' : 'text-slate-500'">
              {{ requirementGroup.done ? 'All document requirements are uploaded.' : 'Upload the missing requirements to complete this checklist.' }}
            </p>
            <button
              v-if="!requirementGroup.done"
              type="button"
              class="inline-flex justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800"
              :aria-expanded="expandedApplicationId === requirementGroup.application.id"
              @click="toggleRequirements(requirementGroup.application.id)"
            >
              {{ expandedApplicationId === requirementGroup.application.id ? 'Hide requirements' : 'Upload requirements' }}
            </button>
          </div>
        </div>

        <div v-if="expandedApplicationId === requirementGroup.application.id" class="border-t border-slate-200 bg-slate-50 p-5">
          <div class="space-y-3">
            <div
              v-for="requirement in requirementGroup.requirementItems"
              :key="`${requirement.applicationId}-${requirement.documentType}`"
              class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center"
            >
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h4 class="text-sm font-bold text-slate-950">{{ requirement.documentType }}</h4>
                  <StatusBadge :status="requirement.status" />
                </div>
                <p class="mt-1 break-all text-sm text-slate-500">
                  {{ requirement.fileName || 'No file uploaded yet' }}
                </p>
              </div>

              <label
                v-if="needsRequirementUpload(requirement)"
                class="inline-flex cursor-pointer items-center justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800"
              >
                {{ uploadingRequirementKey === requirementUploadKey(requirement) ? 'Uploading...' : 'Upload' }}
                <input
                  class="sr-only"
                  type="file"
                  :disabled="Boolean(uploadingRequirementKey)"
                  @change="handleRequirementFileChange(requirementGroup, requirement, $event)"
                />
              </label>
            </div>
          </div>
        </div>
      </article>
    </section>

    <section v-else class="mt-6 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      {{ emptyRequirementsMessage }}
    </section>
  </DashboardLayout>

  <ConfirmationDialog
    :show="Boolean(pendingUpload)"
    title="Upload this document?"
    :message="uploadConfirmationMessage"
    confirm-label="Upload document"
    cancel-label="Cancel"
    :loading="Boolean(uploadingRequirementKey)"
    @confirm="confirmDocumentUpload"
    @close="closeUploadConfirmation"
  />
</template>
