<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const route = useRoute()
const currentUser = getCurrentUser()
const document = ref(null)
const applicant = ref(null)
const loading = ref(true)
const errorMessage = ref('')

async function loadDocument() {
  loading.value = true
  errorMessage.value = ''

  try {
    document.value = await api.getDocument(String(route.params.id))
    applicant.value = document.value.applicationId
      ? await api.getApplication(document.value.applicationId)
      : null
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function setDocumentStatus(verificationStatus) {
  if (!document.value) {
    return
  }

  errorMessage.value = ''

  try {
    document.value = await api.updateDocumentStatus(document.value.id, {
      verificationStatus,
      remarks: verificationStatus === 'Verified'
        ? 'Document is readable and matches applicant details.'
        : 'Please upload a clearer copy for verification.',
    })
  } catch (error) {
    errorMessage.value = error.message
  }
}

onMounted(loadDocument)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Document Details"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading document...
    </section>

    <section v-if="document" class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
      <header class="flex flex-col gap-4 p-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <RouterLink to="/admin/documents" class="text-sm font-bold text-indigo-700 hover:text-indigo-800">
            Back to Documents
          </RouterLink>
          <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ document.documentType }}</h2>
          <p class="mt-2 text-sm text-slate-500">{{ document.studentName }} · Uploaded {{ document.uploadDate }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <StatusBadge :status="document.verificationStatus" />
          <button type="button" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700" @click="setDocumentStatus('Verified')">Verify</button>
          <button type="button" class="rounded-md bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600" @click="setDocumentStatus('For Revision')">Request Revision</button>
        </div>
      </header>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Document Summary</p>
        <div class="mt-5 grid gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Document Type</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.documentType }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">File Name</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.fileName }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Upload Date</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.uploadDate }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status</p>
            <div class="mt-2">
              <StatusBadge :status="document.verificationStatus" />
            </div>
          </div>
        </div>
      </section>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Applicant Information</p>
        <div class="mt-5 grid gap-x-8 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Student Name</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.studentName }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Scholarship Program</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.program }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ applicant?.email || 'Not available' }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Course</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ applicant?.course || 'Not available' }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ applicant?.yearLevel || 'Not available' }}</p>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">GPA</p>
            <p class="mt-1 text-sm font-semibold text-slate-950">{{ applicant?.gpa || 'Not available' }}</p>
          </div>
        </div>
      </section>

      <section class="border-t border-slate-200 p-6">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">File Preview</p>
        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_320px]">
          <div class="grid min-h-72 place-items-center rounded-md border border-dashed border-slate-300 bg-slate-50 p-6">
            <div class="text-center">
              <div class="mx-auto grid h-16 w-16 place-items-center rounded-md bg-indigo-50 text-sm font-black text-indigo-700">
                {{ document.fileType }}
              </div>
              <h3 class="mt-4 text-lg font-bold text-slate-950">{{ document.fileName }}</h3>
              <p class="mt-2 text-sm text-slate-500">Static preview placeholder. File viewer can be connected when uploads are available from the API.</p>
              <button type="button" class="mt-5 rounded-md border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                Open File
              </button>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">File Type</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.fileType }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">File Size</p>
              <p class="mt-1 text-sm font-semibold text-slate-950">{{ document.fileSize }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Verification Remarks</p>
              <p class="mt-2 text-sm leading-7 text-slate-600">{{ document.remarks }}</p>
            </div>
          </div>
        </div>
      </section>
    </section>

    <section v-else-if="!loading" class="rounded-md border border-slate-200 bg-white p-6 text-center shadow-sm">
      <h2 class="text-xl font-bold text-slate-950">Document not found</h2>
      <p class="mt-2 text-sm text-slate-500">The selected document could not be found.</p>
      <RouterLink to="/admin/documents" class="mt-5 inline-flex rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
        Back to Documents
      </RouterLink>
    </section>
  </DashboardLayout>
</template>
