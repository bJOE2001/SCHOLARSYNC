<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import UploadCard from '../../components/uploads/UploadCard.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const uploadMessage = ref('')
const uploadingType = ref('')
const profile = ref(currentUser ?? { name: 'Student' })
const uploadDocuments = ref([])
const currentApplication = ref(null)
const recentNotifications = ref([])

const studentProfile = computed(() => profile.value)

const fallbackRequiredDocuments = [
  'Certificate of Indigency',
  'Report Card',
  'Application Form',
  'Valid ID',
  'Other Supporting Documents',
]

const documentCards = computed(() => {
  if (uploadDocuments.value.length) {
    return uploadDocuments.value
  }

  return fallbackRequiredDocuments.map((documentType) => ({
    id: '',
    applicationId: currentApplication.value?.id ?? '',
    documentType,
    fileName: '',
    status: 'Pending',
  }))
})

async function loadDocuments() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [dashboard, documents] = await Promise.all([
      api.getStudentDashboard(currentUser?.id),
      api.listDocuments({ user_id: currentUser?.id }),
    ])

    profile.value = dashboard.profile
    currentApplication.value = dashboard.currentApplication
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
  uploadingType.value = documentType

  try {
    const formData = new FormData()
    formData.append('documentType', documentType)
    formData.append('userId', currentUser?.id ?? '')
    formData.append('applicationId', applicationId || currentApplication.value?.id || '')
    formData.append('file', file)

    const document = await api.createDocument(formData)
    uploadMessage.value = `${document.documentType} uploaded successfully.`
    await loadDocuments()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    uploadingType.value = ''
  }
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
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Document Checklist</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Upload scholarship requirements</h2>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
        Upload clear and readable copies of your required documents. File status will update after administrator review.
      </p>
    </section>

    <section v-if="loading" class="mt-6 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading documents...
    </section>

    <section v-else class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <UploadCard
        v-for="document in documentCards"
        :key="document.documentType"
        :id="document.id"
        :application-id="document.applicationId"
        :document-type="document.documentType"
        :file-name="document.fileName"
        :status="document.status"
        :uploading="uploadingType === document.documentType"
        @upload="uploadDocument"
      />
    </section>
  </DashboardLayout>
</template>
