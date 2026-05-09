<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const submittedDocuments = ref([])
const loading = ref(true)
const updatingDocument = ref(false)
const pendingDocumentStatus = ref(null)
const errorMessage = ref('')
const currentUser = getCurrentUser()

const columns = [
  { key: 'studentName', label: 'Student Name' },
  { key: 'documentType', label: 'Document Type' },
  { key: 'fileName', label: 'File Name' },
  { key: 'uploadDate', label: 'Upload Date' },
  { key: 'verificationStatus', label: 'Verification Status' },
  { key: 'actions', label: 'Actions' },
]

const documentStatusDialogTitle = computed(() => {
  const pending = pendingDocumentStatus.value

  return pending ? `${pending.verificationStatus} document?` : 'Update document status?'
})

const documentStatusDialogMessage = computed(() => {
  const pending = pendingDocumentStatus.value

  if (!pending) {
    return ''
  }

  return `${pending.document.documentType} for ${pending.document.studentName} will be marked as ${pending.verificationStatus}.`
})

const documentStatusDialogTone = computed(() => pendingDocumentStatus.value?.verificationStatus === 'For Revision' ? 'warning' : 'primary')

function openDocumentStatusConfirmation(document, verificationStatus) {
  errorMessage.value = ''
  pendingDocumentStatus.value = { document, verificationStatus }
}

function closeDocumentStatusConfirmation() {
  if (updatingDocument.value) {
    return
  }

  pendingDocumentStatus.value = null
}

async function loadDocuments() {
  loading.value = true
  errorMessage.value = ''

  try {
    submittedDocuments.value = await api.listDocuments()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function setDocumentStatus(document, verificationStatus) {
  errorMessage.value = ''
  updatingDocument.value = true

  try {
    const updated = await api.updateDocumentStatus(document.id, {
      verificationStatus,
      remarks: verificationStatus === 'Verified'
        ? 'Document is readable and matches applicant details.'
        : 'Please upload a clearer copy for verification.',
    })

    submittedDocuments.value = submittedDocuments.value.map((item) => item.id === updated.id ? updated : item)
    pendingDocumentStatus.value = null
  } catch (error) {
    pendingDocumentStatus.value = null
    errorMessage.value = error.message
  } finally {
    updatingDocument.value = false
  }
}

function confirmDocumentStatusChange() {
  if (!pendingDocumentStatus.value) {
    return
  }

  setDocumentStatus(pendingDocumentStatus.value.document, pendingDocumentStatus.value.verificationStatus)
}

onMounted(loadDocuments)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Document Review"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="mb-6 rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Submitted Documents</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Review and verify uploaded files</h2>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
        Officers can view files, mark them verified, or request corrections from applicants.
      </p>
    </section>

    <p v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading documents...
    </p>

    <DataTable v-else :columns="columns" :rows="submittedDocuments">
      <template #cell-verificationStatus="{ value }">
        <StatusBadge :status="String(value)" />
      </template>
      <template #cell-actions="{ row }">
        <div class="flex flex-wrap gap-2">
          <RouterLink
            :to="`/admin/documents/${row.id}`"
            class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50"
          >
            View
          </RouterLink>
          <button type="button" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700" @click="openDocumentStatusConfirmation(row, 'Verified')">Verify</button>
          <button type="button" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600" @click="openDocumentStatusConfirmation(row, 'For Revision')">Request Revision</button>
        </div>
      </template>
    </DataTable>
  </DashboardLayout>

  <ConfirmationDialog
    :show="Boolean(pendingDocumentStatus)"
    :title="documentStatusDialogTitle"
    :message="documentStatusDialogMessage"
    confirm-label="Update document"
    cancel-label="Cancel"
    :tone="documentStatusDialogTone"
    :loading="updatingDocument"
    @confirm="confirmDocumentStatusChange"
    @close="closeDocumentStatusConfirmation"
  />
</template>
