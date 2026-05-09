<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { studentNavigation } from '../../data/navigation'
import { studentProfile } from '../../data/sampleData'

const profileRows = [
  ['Full Name', studentProfile.name],
  ['Email', studentProfile.email],
  ['Phone Number', studentProfile.phone],
  ['Program of Study', studentProfile.program],
  ['Year Level', studentProfile.yearLevel],
  ['Address', studentProfile.address],
]
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Student Profile"
    role-label="Applicant"
    :notification-count="3"
  >
    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Profile</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Student information</h2>

      <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div v-for="[label, value] in profileRows" :key="label" class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ label }}</p>
          <p class="mt-2 text-sm font-semibold text-slate-950">{{ value }}</p>
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>
