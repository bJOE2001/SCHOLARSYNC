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
const pendingStatus = ref('')
const errorMessage = ref('')

const relatedDocuments = computed(() => {
  return application.value?.documents ?? []
})

const documentColumns = [
  { key: 'documentType', label: 'Document Type' },
  { key: 'fileName', label: 'File Name' },
  { key: 'uploadDate', label: 'Upload Date' },
  { key: 'verificationStatus', label: 'Status' },
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

function openStatusConfirmation(status) {
  errorMessage.value = ''
  pendingStatus.value = status
}

function closeStatusConfirmation() {
  if (updatingStatus.value) {
    return
  }

  pendingStatus.value = ''
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

async function setStatus(status) {
  if (!application.value) {
    return
  }

  errorMessage.value = ''
  updatingStatus.value = true

  try {
    application.value = await api.updateApplicationStatus(application.value.id, {
      status,
      remarks: status === 'Approved'
        ? 'Approved for the current scholarship cycle.'
        : status === 'Rejected'
          ? 'Application was rejected after review.'
          : 'Please revise the flagged requirements.',
    })
    pendingStatus.value = ''
  } catch (error) {
    pendingStatus.value = ''
    errorMessage.value = error.message
  } finally {
    updatingStatus.value = false
  }
}

function confirmStatusChange() {
  if (!pendingStatus.value) {
    return
  }

  setStatus(pendingStatus.value)
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
          >
            <template #cell-verificationStatus="{ value }">
              <StatusBadge :status="String(value)" />
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
    @confirm="confirmStatusChange"
    @close="closeStatusConfirmation"
  />
</template>
