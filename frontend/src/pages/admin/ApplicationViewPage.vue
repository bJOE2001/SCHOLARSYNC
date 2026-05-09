<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { applications, submittedDocuments } from '../../data/sampleData'

const route = useRoute()

const application = computed(() => {
  const id = String(route.params.id)

  return applications.find((item) => item.id === id)
})

const relatedDocuments = computed(() => {
  if (!application.value) {
    return []
  }

  return submittedDocuments.filter((document) => document.studentName === application.value?.applicantName)
})

const documentColumns = [
  { key: 'documentType', label: 'Document Type' },
  { key: 'fileName', label: 'File Name' },
  { key: 'uploadDate', label: 'Upload Date' },
  { key: 'verificationStatus', label: 'Status' },
]
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    user-name="Dr. Camille Navarro"
    context="Application Details"
    role-label="Scholarship Officer"
    :notification-count="8"
  >
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
          <button type="button" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Approve</button>
          <button type="button" class="rounded-md bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700">Reject</button>
          <button type="button" class="rounded-md bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">Request Revision</button>
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

    <section v-else class="rounded-md border border-slate-200 bg-white p-6 text-center shadow-sm">
      <h2 class="text-xl font-bold text-slate-950">Application not found</h2>
      <p class="mt-2 text-sm text-slate-500">The selected application could not be found.</p>
      <RouterLink to="/admin/applications" class="mt-5 inline-flex rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
        Back to Applications
      </RouterLink>
    </section>
  </DashboardLayout>
</template>
