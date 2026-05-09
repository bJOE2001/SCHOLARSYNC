<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { applications } from '../../data/sampleData'

const searchQuery = ref('')
const selectedStatus = ref('All')

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

  return applications.filter((application) => {
    const matchesSearch =
      application.applicantName.toLowerCase().includes(query) ||
      application.program.toLowerCase().includes(query)
    const matchesStatus = selectedStatus.value === 'All' || application.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    user-name="Dr. Camille Navarro"
    context="Applications Management"
    role-label="Scholarship Officer"
    :notification-count="8"
  >
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
      <DataTable :columns="columns" :rows="filteredApplications">
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
            <button type="button" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve</button>
            <button type="button" class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Reject</button>
            <button type="button" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">Request Revision</button>
          </div>
        </template>
      </DataTable>
    </section>
  </DashboardLayout>
</template>
