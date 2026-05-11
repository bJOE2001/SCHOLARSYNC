<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const route = useRoute()
const currentUser = getCurrentUser()
const application = ref(null)
const loading = ref(true)
const updatingStatus = ref(false)
const updatingDocument = ref(false)
const pendingStatus = ref('')
const pendingDocumentStatus = ref(null)
const selectedDocument = ref(null)
const applicationRevisionReason = ref('')
const documentRevisionReason = ref('')
const errorMessage = ref('')

const relatedDocuments = computed(() => {
  return application.value?.documents ?? []
})

const documentColumns = [
  { key: 'documentType', label: 'Document Type' },
  { key: 'fileName', label: 'File Name' },
  { key: 'uploadDate', label: 'Upload Date' },
  { key: 'verificationStatus', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const statusDialogTitle = computed(() => pendingStatus.value ? `${pendingStatus.value} application?` : 'Update application status?')
const statusDialogMessage = computed(() => {
  if (!application.value || !pendingStatus.value) {
    return ''
  }

  return `${application.value.applicantName}'s application will be marked as ${pendingStatus.value}.`
})
const statusDialogTone = computed(() => {
  if (pendingStatus.value === 'Rejected') {
    return 'danger'
  }

  if (pendingStatus.value === 'For Revision') {
    return 'warning'
  }

  return 'primary'
})
const isApplicationRevisionRequest = computed(() => pendingStatus.value === 'For Revision')
const hasApplicationRevisionReason = computed(() => applicationRevisionReason.value.trim().length > 0)
const documentStatusDialogTitle = computed(() => {
  const pending = pendingDocumentStatus.value

  return pending ? `${pending.verificationStatus} document?` : 'Update document status?'
})
const documentStatusDialogMessage = computed(() => {
  const pending = pendingDocumentStatus.value

  if (!pending) {
    return ''
  }

  return `${pending.document.documentType} for ${application.value?.applicantName ?? 'this applicant'} will be marked as ${pending.verificationStatus}.`
})
const documentStatusDialogTone = computed(() => pendingDocumentStatus.value?.verificationStatus === 'For Revision' ? 'warning' : 'primary')
const isDocumentRevisionRequest = computed(() => pendingDocumentStatus.value?.verificationStatus === 'For Revision')
const hasDocumentRevisionReason = computed(() => documentRevisionReason.value.trim().length > 0)
const selectedDocumentFileUrl = computed(() => resolveFileUrl(selectedDocument.value?.fileUrl))
const selectedDocumentPreviewKind = computed(() => {
  const document = selectedDocument.value
  const fileName = document?.fileName?.toLowerCase() ?? ''
  const fileType = document?.fileType?.toLowerCase() ?? ''

  if (['jpg', 'jpeg', 'png', 'gif', 'webp'].some((type) => fileType.includes(type) || fileName.endsWith(`.${type}`))) {
    return 'image'
  }

  if (fileType.includes('pdf') || fileName.endsWith('.pdf')) {
    return 'pdf'
  }

  return 'file'
})

function openStatusConfirmation(status) {
  errorMessage.value = ''
  applicationRevisionReason.value = ''
  pendingStatus.value = status
}

function closeStatusConfirmation() {
  if (updatingStatus.value) {
    return
  }

  pendingStatus.value = ''
  applicationRevisionReason.value = ''
}

function openDocumentStatusConfirmation(document, verificationStatus) {
  errorMessage.value = ''
  documentRevisionReason.value = ''
  pendingDocumentStatus.value = { document, verificationStatus }
}

function closeDocumentStatusConfirmation() {
  if (updatingDocument.value) {
    return
  }

  pendingDocumentStatus.value = null
  documentRevisionReason.value = ''
}

function resolveFileUrl(fileUrl) {
  if (!fileUrl) {
    return ''
  }

  if (/^https?:\/\//i.test(fileUrl)) {
    return fileUrl
  }

  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'

  try {
    const origin = new URL(apiBaseUrl, window.location.origin).origin
    const path = fileUrl.startsWith('/') ? fileUrl : `/${fileUrl}`

    return `${origin}${path}`
  } catch {
    return fileUrl
  }
}

function openDocumentPreview(document) {
  selectedDocument.value = document
}

function closeDocumentPreview() {
  selectedDocument.value = null
}

async function loadApplication() {
  loading.value = true
  errorMessage.value = ''

  try {
    application.value = await api.getApplication(String(route.params.id))
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

function applicationStatusRemarks(status, reason = '') {
  if (status === 'Approved') {
    return 'Approved for the current scholarship cycle.'
  }

  if (status === 'Rejected') {
    return 'Application was rejected after review.'
  }

  return reason.trim() || 'Please revise the flagged requirements.'
}

function documentStatusRemarks(verificationStatus, reason = '') {
  if (verificationStatus === 'Verified') {
    return 'Document is readable and matches applicant details.'
  }

  return reason.trim() || 'Please upload a clearer copy for verification.'
}

async function setStatus(status, reason = '') {
  if (!application.value) {
    return
  }

  errorMessage.value = ''
  updatingStatus.value = true

  try {
    application.value = await api.updateApplicationStatus(application.value.id, {
      status,
      remarks: applicationStatusRemarks(status, reason),
    })
    pendingStatus.value = ''
    applicationRevisionReason.value = ''
  } catch (error) {
    pendingStatus.value = ''
    applicationRevisionReason.value = ''
    errorMessage.value = error.message
  } finally {
    updatingStatus.value = false
  }
}

async function setDocumentStatus(document, verificationStatus, reason = '') {
  if (!application.value) {
    return
  }

  errorMessage.value = ''
  updatingDocument.value = true

  try {
    const updated = await api.updateDocumentStatus(document.id, {
      verificationStatus,
      remarks: documentStatusRemarks(verificationStatus, reason),
    })

    application.value = {
      ...application.value,
      documents: relatedDocuments.value.map((item) => item.id === updated.id ? updated : item),
    }

    if (selectedDocument.value?.id === updated.id) {
      selectedDocument.value = updated
    }

    pendingDocumentStatus.value = null
    documentRevisionReason.value = ''
  } catch (error) {
    pendingDocumentStatus.value = null
    documentRevisionReason.value = ''
    errorMessage.value = error.message
  } finally {
    updatingDocument.value = false
  }
}

function confirmStatusChange() {
  if (!pendingStatus.value) {
    return
  }

  if (isApplicationRevisionRequest.value && !hasApplicationRevisionReason.value) {
    return
  }

  setStatus(pendingStatus.value, applicationRevisionReason.value)
}

function confirmDocumentStatusChange() {
  if (!pendingDocumentStatus.value) {
    return
  }

  if (isDocumentRevisionRequest.value && !hasDocumentRevisionReason.value) {
    return
  }

  setDocumentStatus(
    pendingDocumentStatus.value.document,
    pendingDocumentStatus.value.verificationStatus,
    documentRevisionReason.value,
  )
}

function handleDocumentTableAction({ action, row }) {
  if (action === 'View Document') {
    openDocumentPreview(row)
    return
  }

  if (['Verified', 'For Revision'].includes(action)) {
    openDocumentStatusConfirmation(row, action)
  }
}

onMounted(loadApplication)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Application Details"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading application...
    </section>

    <section v-if="application" class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
      <header class="flex flex-col gap-4 p-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <RouterLink to="/admin/applications" class="text-sm font-bold text-indigo-700 hover:text-indigo-800">
            Back to Applications
          </RouterLink>
          <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ application.applicantName }}</h2>
          <p class="mt-2 text-sm text-slate-500">{{ application.program }} · Submitted {{ application.dateSubmitted }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <StatusBadge :status="application.status" />
          <button type="button" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700" @click="openStatusConfirmation('Approved')">Approve</button>
          <button type="button" class="rounded-md bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700" @click="openStatusConfirmation('Rejected')">Reject</button>
          <button type="button" class="rounded-md bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600" @click="openStatusConfirmation('For Revision')">Request Revision</button>
        </div>
      </header>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Application Summary</p>
        <div class="mt-5 grid gap-x-8 gap-y-5 md:grid-cols-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Scholarship Program</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.program }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Date Submitted</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.dateSubmitted }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Current Status</p>
            <div class="mt-2">
              <StatusBadge :status="application.status" />
            </div>
          </div>
        </div>
      </section>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Applicant Information</p>
        <div class="mt-5 grid gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Full Name</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.applicantName }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.email }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Phone</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.phone }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Course</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.course }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.yearLevel }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">GPA</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.gpa }}</p>
          </div>
          <div class="md:col-span-2 xl:col-span-3">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Address</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ application.address }}</p>
          </div>
        </div>
      </section>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Reason for Applying</p>
        <p class="mt-4 text-sm leading-7 text-slate-600">{{ application.reason }}</p>
      </section>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Officer Remarks</p>
        <p class="mt-4 text-sm leading-7 text-slate-600">{{ application.remarks }}</p>
      </section>

      <section class="border-t border-slate-200 p-6">
        <div>
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Submitted Documents</p>
          <h2 class="mt-2 text-xl font-bold text-slate-950">Applicant document checklist</h2>
        </div>

        <div class="mt-5">
          <DataTable
            :columns="documentColumns"
            :rows="relatedDocuments"
            :initial-per-page="2"
            :per-page-options="[2, 4, 6]"
            empty-text="No uploaded documents found for this applicant."
            @row-action="handleDocumentTableAction"
          >
            <template #cell-verificationStatus="{ value }">
              <StatusBadge :status="String(value)" />
            </template>
            <template #cell-actions="{ row }">
              <div class="flex flex-wrap gap-2">
                <button type="button" data-table-action="View Document" class="rounded-md bg-indigo-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-800">
                  View document
                </button>
                <button type="button" data-table-action="Verified" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">
                  Verify
                </button>
                <button type="button" data-table-action="For Revision" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">
                  Request Revision
                </button>
              </div>
            </template>
          </DataTable>
        </div>
      </section>
    </section>

    <section v-else-if="!loading" class="rounded-md border border-slate-200 bg-white p-6 text-center shadow-sm">
      <h2 class="text-xl font-bold text-slate-950">Application not found</h2>
      <p class="mt-2 text-sm text-slate-500">The selected application could not be found.</p>
      <RouterLink to="/admin/applications" class="mt-5 inline-flex rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
        Back to Applications
      </RouterLink>
    </section>
  </DashboardLayout>

  <ConfirmationDialog
    :show="Boolean(pendingStatus)"
    :title="statusDialogTitle"
    :message="statusDialogMessage"
    confirm-label="Update status"
    cancel-label="Cancel"
    :tone="statusDialogTone"
    :loading="updatingStatus"
    :confirm-disabled="isApplicationRevisionRequest && !hasApplicationRevisionReason"
    @confirm="confirmStatusChange"
    @close="closeStatusConfirmation"
  >
    <label v-if="isApplicationRevisionRequest" class="block">
      <span class="mb-2 block text-sm font-bold text-slate-700">Reason for revision</span>
      <textarea
        v-model="applicationRevisionReason"
        rows="4"
        class="w-full resize-none rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        placeholder="Explain what the applicant needs to revise."
      ></textarea>
      <span v-if="!hasApplicationRevisionReason" class="mt-2 block text-xs font-semibold text-amber-700">A revision reason is required.</span>
    </label>
  </ConfirmationDialog>

  <ConfirmationDialog
    :show="Boolean(pendingDocumentStatus)"
    :title="documentStatusDialogTitle"
    :message="documentStatusDialogMessage"
    confirm-label="Update document"
    cancel-label="Cancel"
    :tone="documentStatusDialogTone"
    :loading="updatingDocument"
    :confirm-disabled="isDocumentRevisionRequest && !hasDocumentRevisionReason"
    @confirm="confirmDocumentStatusChange"
    @close="closeDocumentStatusConfirmation"
  >
    <label v-if="isDocumentRevisionRequest" class="block">
      <span class="mb-2 block text-sm font-bold text-slate-700">Reason for revision</span>
      <textarea
        v-model="documentRevisionReason"
        rows="4"
        class="w-full resize-none rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        placeholder="Explain what document issue needs to be corrected."
      ></textarea>
      <span v-if="!hasDocumentRevisionReason" class="mt-2 block text-xs font-semibold text-amber-700">A revision reason is required.</span>
    </label>
  </ConfirmationDialog>

  <Teleport to="body">
    <div
      v-if="selectedDocument"
      class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4"
      aria-modal="true"
      role="dialog"
      aria-labelledby="application-document-preview-title"
      @click.self="closeDocumentPreview"
    >
      <section class="w-full max-w-4xl overflow-hidden rounded-md bg-white shadow-xl">
        <header class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">Uploaded Requirement</p>
            <h2 id="application-document-preview-title" class="mt-2 text-xl font-bold text-slate-950">{{ selectedDocument.documentType }}</h2>
            <p class="mt-2 break-all text-sm text-slate-500">{{ selectedDocument.fileName || 'No file name' }}</p>
          </div>
          <button type="button" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeDocumentPreview">
            Close
          </button>
        </header>

        <div class="max-h-[78vh] overflow-y-auto p-5">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
              <StatusBadge :status="selectedDocument.verificationStatus" />
              <span class="rounded-md bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                Uploaded {{ selectedDocument.uploadDate || 'date not available' }}
              </span>
            </div>

            <div class="flex flex-wrap gap-2">
              <a
                v-if="selectedDocumentFileUrl"
                :href="selectedDocumentFileUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-800"
              >
                Open file
              </a>
              <button type="button" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700" @click="openDocumentStatusConfirmation(selectedDocument, 'Verified')">
                Verify
              </button>
              <button type="button" class="rounded-md bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600" @click="openDocumentStatusConfirmation(selectedDocument, 'For Revision')">
                Request Revision
              </button>
            </div>
          </div>

          <div class="mt-5 grid min-h-96 place-items-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50 p-4">
            <img
              v-if="selectedDocumentFileUrl && selectedDocumentPreviewKind === 'image'"
              :src="selectedDocumentFileUrl"
              :alt="selectedDocument.fileName"
              class="max-h-[56vh] max-w-full rounded-md object-contain"
            />
            <iframe
              v-else-if="selectedDocumentFileUrl && selectedDocumentPreviewKind === 'pdf'"
              :src="selectedDocumentFileUrl"
              :title="selectedDocument.fileName"
              class="h-[56vh] w-full rounded-md bg-white"
            ></iframe>
            <div v-else class="text-center">
              <div class="mx-auto grid h-16 w-16 place-items-center rounded-md bg-indigo-50 text-sm font-black text-indigo-700">
                {{ selectedDocument.fileType || 'FILE' }}
              </div>
              <h3 class="mt-4 text-base font-bold text-slate-950">Preview unavailable</h3>
              <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                This file type may need to be opened in a separate tab.
              </p>
            </div>
          </div>

          <div class="mt-5 grid gap-4 rounded-md border border-slate-200 p-4 sm:grid-cols-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">File Type</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ selectedDocument.fileType || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">File Size</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ selectedDocument.fileSize || 'Not available' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Remarks</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ selectedDocument.remarks || 'No remarks yet' }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </Teleport>
</template>
