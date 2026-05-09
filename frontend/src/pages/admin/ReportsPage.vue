<script setup>
import { onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import FormInput from '../../components/forms/FormInput.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const filters = reactive({
  program: '',
  dateRange: '',
  status: '',
})
const reportPreviewRows = ref([])
const loading = ref(true)
const errorMessage = ref('')
const currentUser = getCurrentUser()

const programOptions = [
  { label: 'All Programs', value: 'All Programs' },
  { label: 'Academic Excellence Grant', value: 'Academic Excellence Grant' },
  { label: 'Financial Assistance Program', value: 'Financial Assistance Program' },
  { label: 'STEM Scholarship', value: 'STEM Scholarship' },
]

const dateRangeOptions = [
  { label: 'This Month', value: 'This Month' },
  { label: 'This Quarter', value: 'This Quarter' },
  { label: 'This Academic Year', value: 'This Academic Year' },
]

const statusOptions = [
  { label: 'All Statuses', value: 'All Statuses' },
  { label: 'Pending', value: 'Pending' },
  { label: 'Under Review', value: 'Under Review' },
  { label: 'Approved', value: 'Approved' },
  { label: 'For Revision', value: 'For Revision' },
]

const columns = [
  { key: 'applicantName', label: 'Applicant Name' },
  { key: 'program', label: 'Scholarship Program' },
  { key: 'status', label: 'Status' },
  { key: 'submitted', label: 'Date Submitted' },
]

async function loadReport() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.getApplicationReport(filters)
    reportPreviewRows.value = response.rows ?? []
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadReport)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Reports"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Report Filters</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Generate scholarship reports</h2>

      <div class="mt-6 grid gap-5 lg:grid-cols-3">
        <FormInput id="report-program" v-model="filters.program" label="Scholarship Program" :options="programOptions" />
        <FormInput id="report-date-range" v-model="filters.dateRange" label="Date Range" :options="dateRangeOptions" />
        <FormInput id="report-status" v-model="filters.status" label="Status" :options="statusOptions" />
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <button type="button" class="rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800" @click="loadReport">Generate Report</button>
        <button type="button" class="rounded-md border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Export PDF</button>
        <button type="button" class="rounded-md border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Export Excel</button>
      </div>
    </section>

    <section class="mt-6">
      <div class="mb-4">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Report Preview</p>
        <h2 class="mt-2 text-xl font-bold text-slate-950">Sample generated results</h2>
      </div>
      <p v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
        Loading report...
      </p>

      <DataTable v-else :columns="columns" :rows="reportPreviewRows">
        <template #cell-status="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
      </DataTable>
    </section>
  </DashboardLayout>
</template>
