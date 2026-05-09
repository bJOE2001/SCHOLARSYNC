<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { adminNavigation } from '../../data/navigation'

const officerProfile = [
  ['Full Name', 'Dr. Camille Navarro'],
  ['Role', 'Scholarship Officer'],
  ['Assigned Program', 'Academic Excellence Grant'],
  ['Office', 'College Scholarship Office'],
  ['Email', 'camille.navarro@college.edu'],
  ['Contact Number', '0917 882 4401'],
]
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    user-name="Dr. Camille Navarro"
    context="Officer Profile"
    role-label="Scholarship Officer"
    :notification-count="8"
  >
    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Profile</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Scholarship officer information</h2>

      <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div v-for="[label, value] in officerProfile" :key="label" class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ label }}</p>
          <p class="mt-2 text-sm font-semibold text-slate-950">{{ value }}</p>
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>
