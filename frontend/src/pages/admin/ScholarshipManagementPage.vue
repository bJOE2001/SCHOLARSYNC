<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import FormInput from '../../components/forms/FormInput.vue'
import ConfirmationDialog from '../../components/ui/ConfirmationDialog.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const searchQuery = ref('')
const selectedStatus = ref('All')
const scholarships = ref([])
const loading = ref(true)
const saving = ref(false)
const showSaveConfirmation = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const currentUser = getCurrentUser()

const form = reactive({
  scholarshipName: '',
  description: '',
  eligibilityRequirements: '',
  requiredDocuments: '',
  deadline: '',
  availableSlots: '',
  announcementDetails: '',
  status: 'Draft',
  scholarshipType: '',
  academicYear: '',
  semester: '',
  minimumGpa: '',
  yearLevelAllowed: '',
  programAllowed: '',
  contactPerson: '',
  datePosted: '',
})

const statusOptions = [
  { label: 'Open', value: 'Open' },
  { label: 'Closed', value: 'Closed' },
  { label: 'Draft', value: 'Draft' },
]

const statusFilterOptions = ['All', 'Open', 'Closed', 'Draft']

const scholarshipTypeOptions = [
  { label: 'Merit-Based', value: 'Merit-Based' },
  { label: 'Need-Based', value: 'Need-Based' },
  { label: 'Program-Based', value: 'Program-Based' },
  { label: 'Leadership Grant', value: 'Leadership Grant' },
]

const semesterOptions = [
  { label: '1st Semester', value: '1st Semester' },
  { label: '2nd Semester', value: '2nd Semester' },
  { label: 'Summer', value: 'Summer' },
]

const yearLevelOptions = [
  { label: 'All Year Levels', value: 'All Year Levels' },
  { label: '1st Year Only', value: '1st Year Only' },
  { label: '2nd Year to 4th Year', value: '2nd Year to 4th Year' },
  { label: '3rd Year to 4th Year', value: '3rd Year to 4th Year' },
]

const columns = [
  { key: 'scholarshipName', label: 'Scholarship Name' },
  { key: 'scholarshipType', label: 'Type' },
  { key: 'academicYear', label: 'Academic Year' },
  { key: 'deadline', label: 'Deadline' },
  { key: 'availableSlots', label: 'Slots' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const filteredScholarships = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  return scholarships.value.filter((scholarship) => {
    const matchesSearch =
      scholarship.scholarshipName.toLowerCase().includes(query) ||
      scholarship.scholarshipType.toLowerCase().includes(query)
    const matchesStatus = selectedStatus.value === 'All' || scholarship.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

function resetForm() {
  Object.assign(form, {
    scholarshipName: '',
    description: '',
    eligibilityRequirements: '',
    requiredDocuments: '',
    deadline: '',
    availableSlots: '',
    announcementDetails: '',
    status: 'Draft',
    scholarshipType: '',
    academicYear: '',
    semester: '',
    minimumGpa: '',
    yearLevelAllowed: '',
    programAllowed: '',
    contactPerson: '',
    datePosted: '',
  })
}

function openSaveConfirmation() {
  errorMessage.value = ''
  successMessage.value = ''
  showSaveConfirmation.value = true
}

function closeSaveConfirmation() {
  if (saving.value) {
    return
  }

  showSaveConfirmation.value = false
}

async function loadScholarships() {
  loading.value = true
  errorMessage.value = ''

  try {
    scholarships.value = await api.listScholarships()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

async function saveScholarship() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const scholarship = await api.createScholarship(form)
    scholarships.value = [scholarship, ...scholarships.value]
    successMessage.value = 'Scholarship saved successfully.'
    showSaveConfirmation.value = false
    resetForm()
  } catch (error) {
    showSaveConfirmation.value = false
    errorMessage.value = error.message
  } finally {
    saving.value = false
  }
}

onMounted(loadScholarships)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Scholarship Management"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="successMessage" class="mb-5 rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
      {{ successMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Scholarship Management Module</p>
          <h2 class="mt-2 text-xl font-bold text-slate-950">Create scholarship program</h2>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
            Configure scholarship details, eligibility, requirements, deadlines, announcement content, and publication status.
          </p>
        </div>
        <button type="button" class="rounded-md border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="resetForm">
          Clear Form
        </button>
      </div>

      <form class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3" @submit.prevent="openSaveConfirmation">
        <FormInput id="scholarship-name" v-model="form.scholarshipName" label="Scholarship Name" placeholder="Academic Excellence Grant" />
        <FormInput id="scholarship-type" v-model="form.scholarshipType" label="Scholarship Type" :options="scholarshipTypeOptions" />
        <FormInput id="academic-year" v-model="form.academicYear" label="Academic Year" placeholder="2026-2027" />
        <FormInput id="semester" v-model="form.semester" label="Semester" :options="semesterOptions" />
        <FormInput id="minimum-gpa" v-model="form.minimumGpa" label="Minimum GPA" placeholder="1.75" />
        <FormInput id="year-level" v-model="form.yearLevelAllowed" label="Year Level Allowed" :options="yearLevelOptions" />
        <FormInput id="program-allowed" v-model="form.programAllowed" label="Program/Course Allowed" placeholder="All Programs or BSIT, BSCS" />
        <FormInput id="available-slots" v-model="form.availableSlots" label="Available Slots" type="number" placeholder="100" />
        <FormInput id="deadline" v-model="form.deadline" label="Deadline" type="date" />
        <FormInput id="date-posted" v-model="form.datePosted" label="Date Posted" type="date" />
        <FormInput id="contact-person" v-model="form.contactPerson" label="Contact Person" placeholder="Scholarship Office" />
        <FormInput id="status" v-model="form.status" label="Status" :options="statusOptions" />
        <div class="grid gap-5 md:col-span-2 md:grid-cols-2 xl:col-span-3">
          <FormInput id="description" v-model="form.description" label="Description" placeholder="Describe the scholarship program." textarea :rows="3" />
          <FormInput id="eligibility" v-model="form.eligibilityRequirements" label="Eligibility Requirements" placeholder="Minimum GPA, enrollment status, good moral standing, etc." textarea :rows="3" />
          <FormInput id="required-documents" v-model="form.requiredDocuments" label="Required Documents" placeholder="Report Card, Valid ID, Certificate of Enrollment, etc." textarea :rows="3" />
          <FormInput id="announcement-details" v-model="form.announcementDetails" label="Announcement Details" placeholder="Write the announcement details shown to applicants." textarea :rows="3" />
        </div>
        <div class="md:col-span-2 xl:col-span-3">
          <button type="submit" class="rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Scholarship' }}
          </button>
        </div>
      </form>
    </section>

    <section class="mt-6 rounded-md border border-slate-200 bg-white p-5 shadow-sm">
      <div class="grid gap-4 lg:grid-cols-[1fr_180px]">
        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Search scholarships</span>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search by scholarship name or type"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          />
        </label>
        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Status</span>
          <select
            v-model="selectedStatus"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          >
            <option v-for="status in statusFilterOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </label>
      </div>
    </section>

    <section class="mt-6">
      <p v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
        Loading scholarships...
      </p>

      <DataTable
        v-else
        :columns="columns"
        :rows="filteredScholarships"
        :initial-per-page="5"
        :per-page-options="[5, 10, 15]"
        empty-text="No scholarships found."
      >
        <template #cell-status="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
        <template #cell-actions>
          <div class="flex flex-wrap gap-2">
            <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">Edit</button>
            <button type="button" class="rounded-md bg-indigo-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-800">Publish</button>
            <button type="button" class="rounded-md bg-slate-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800">Close</button>
          </div>
        </template>
      </DataTable>
    </section>
  </DashboardLayout>

  <ConfirmationDialog
    :show="showSaveConfirmation"
    title="Create this scholarship?"
    :message="`${form.scholarshipName || 'This scholarship'} will be saved with ${form.status || 'Draft'} status.`"
    confirm-label="Save scholarship"
    cancel-label="Cancel"
    :loading="saving"
    @confirm="saveScholarship"
    @close="closeSaveConfirmation"
  />
</template>
