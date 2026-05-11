<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const searchQuery = ref('')
const selectedStatus = ref('All')
const applications = ref([])
const loading = ref(true)
const updatingStatus = ref(false)
const pendingStatusChange = ref(null)
const revisionReason = ref('')
const errorMessage = ref('')
const currentUser = getCurrentUser()

const statusOptions = ['All', 'Pending', 'Under Review', 'Approved', 'Rejected', 'For Revision']
const columns = [
  { key: 'applicantName', label: 'Applicant Name' },
  { key: 'program', label: 'Program' },
  { key: 'dateSubmitted', label: 'Date Submitted' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const filteredApplications = computed(() => {
  const query = searchQuery.value.toLowerCase()

  return applications.value.filter((application) => {
    const matchesSearch =
      application.applicantName.toLowerCase().includes(query) ||
      application.program.toLowerCase().includes(query)
    const matchesStatus = selectedStatus.value === 'All' || application.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

const statusDialogTitle = computed(() => {
  const pending = pendingStatusChange.value

  return pending ? `${pending.status} application?` : 'Update application status?'
})

const statusDialogMessage = computed(() => {
  const pending = pendingStatusChange.value

  if (!pending) {
    return ''
  }

  return `${pending.application.applicantName}'s application will be marked as ${pending.status}.`
})

const statusDialogTone = computed(() => {
  const status = pendingStatusChange.value?.status

  if (status === 'Rejected') {
    return 'danger'
  }

  if (status === 'For Revision') {
    return 'warning'
  }

  return 'primary'
})
const isRevisionRequest = computed(() => pendingStatusChange.value?.status === 'For Revision')
const hasRevisionReason = computed(() => revisionReason.value.trim().length > 0)

function openStatusConfirmation(application, status) {
  errorMessage.value = ''
  revisionReason.value = ''
  pendingStatusChange.value = { application, status }
}

function closeStatusConfirmation() {
  if (updatingStatus.value) {
    return
  }

  pendingStatusChange.value = null
  revisionReason.value = ''
}

async function loadApplications() {
  loading.value = true
  errorMessage.value = ''

  try {
    applications.value = await api.listApplications()
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

async function setApplicationStatus(application, status, reason = '') {
  errorMessage.value = ''
  updatingStatus.value = true

  try {
    const updated = await api.updateApplicationStatus(application.id, {
      status,
      remarks: applicationStatusRemarks(status, reason),
    })

    applications.value = applications.value.map((item) => item.id === updated.id ? updated : item)
    pendingStatusChange.value = null
    revisionReason.value = ''
  } catch (error) {
    pendingStatusChange.value = null
    revisionReason.value = ''
    errorMessage.value = error.message
  } finally {
    updatingStatus.value = false
  }
}

function confirmStatusChange() {
  if (!pendingStatusChange.value) {
    return
  }

  if (isRevisionRequest.value && !hasRevisionReason.value) {
    return
  }

  setApplicationStatus(pendingStatusChange.value.application, pendingStatusChange.value.status, revisionReason.value)
}

function handleApplicationTableAction({ action, row }) {
  if (['Approved', 'Rejected', 'For Revision'].includes(action)) {
    openStatusConfirmation(row, action)
  }
}

onMounted(loadApplications)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Applications Management"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
      <div class="grid gap-4 lg:grid-cols-[1fr_220px]">
        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Search applicants</span>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search by name or program"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Status Filter</span>
          <select
            v-model="selectedStatus"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          >
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </label>
      </div>
    </section>

    <section class="mt-6">
      <p v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
        Loading applications...
      </p>

      <DataTable v-else :columns="columns" :rows="filteredApplications" @row-action="handleApplicationTableAction">
        <template #cell-status="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
        <template #cell-actions="{ row }">
          <div class="flex flex-wrap gap-2">
            <RouterLink
              :to="`/admin/applications/${row.id}`"
              class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50"
            >
              View
            </RouterLink>
            <button type="button" data-table-action="Approved" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve</button>
            <button type="button" data-table-action="Rejected" class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Reject</button>
            <button type="button" data-table-action="For Revision" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">Request Revision</button>
          </div>
        </template>
      </DataTable>
    </section>
  </DashboardLayout>

  <ConfirmationDialog
    :show="Boolean(pendingStatusChange)"
    :title="statusDialogTitle"
    :message="statusDialogMessage"
    confirm-label="Update status"
    cancel-label="Cancel"
    :tone="statusDialogTone"
    :loading="updatingStatus"
    :confirm-disabled="isRevisionRequest && !hasRevisionReason"
    @confirm="confirmStatusChange"
    @close="closeStatusConfirmation"
  >
    <label v-if="isRevisionRequest" class="block">
      <span class="mb-2 block text-sm font-bold text-slate-700">Reason for revision</span>
      <textarea
        v-model="revisionReason"
        rows="4"
        class="w-full resize-none rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
        placeholder="Explain what the applicant needs to revise."
      ></textarea>
      <span v-if="!hasRevisionReason" class="mt-2 block text-xs font-semibold text-amber-700">A revision reason is required.</span>
    </label>
  </ConfirmationDialog>
</template>
