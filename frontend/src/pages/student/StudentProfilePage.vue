<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { studentNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const profile = ref(currentUser ?? { name: 'Student' })

const studentProfile = computed(() => profile.value)
const profileRows = computed(() => [
  ['Full Name', studentProfile.value.name],
  ['Email', studentProfile.value.email],
  ['Phone Number', studentProfile.value.phone],
  ['Program of Study', studentProfile.value.program],
  ['Year Level', studentProfile.value.yearLevel],
  ['Address', studentProfile.value.address],
])

async function loadProfile() {
  loading.value = true
  errorMessage.value = ''

  try {
    profile.value = await api.getStudentProfile(currentUser?.id)
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadProfile)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Student Profile"
    role-label="Applicant"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Profile</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Student information</h2>

      <p v-if="loading" class="mt-6 rounded-md bg-slate-50 p-4 text-sm font-semibold text-slate-500">
        Loading profile...
      </p>

      <div v-else class="mt-6 grid gap-4 md:grid-cols-2">
        <div v-for="[label, value] in profileRows" :key="label" class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ label }}</p>
          <p class="mt-2 text-sm font-semibold text-slate-950">{{ value || 'Not provided' }}</p>
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>
