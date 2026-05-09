<script setup>
import { onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const submittedDocuments = ref([])
const loading = ref(true)
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

  try {
    const updated = await api.updateDocumentStatus(document.id, {
      verificationStatus,
      remarks: verificationStatus === 'Verified'
        ? 'Document is readable and matches applicant details.'
        : 'Please upload a clearer copy for verification.',
    })

    submittedDocuments.value = submittedDocuments.value.map((item) => item.id === updated.id ? updated : item)
  } catch (error) {
    errorMessage.value = error.message
  }
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
          <button type="button" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700" @click="setDocumentStatus(row, 'Verified')">Verify</button>
          <button type="button" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600" @click="setDocumentStatus(row, 'For Revision')">Request Revision</button>
        </div>
      </template>
    </DataTable>
  </DashboardLayout>
</template>
