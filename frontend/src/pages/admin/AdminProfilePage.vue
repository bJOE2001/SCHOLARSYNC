<script setup>
import { computed } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { adminNavigation } from '../../data/navigation'
import { getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()

const officerProfile = computed(() => [
  ['Full Name', currentUser?.name || 'Scholarship Officer'],
  ['Role', 'Scholarship Officer'],
  ['Assigned Program', currentUser?.program || 'Scholarship Office'],
  ['Office', 'College Scholarship Office'],
  ['Email', currentUser?.email || 'Not provided'],
  ['Contact Number', currentUser?.phone || 'Not provided'],
])
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Officer Profile"
    role-label="Scholarship Officer"
    :notification-count="0"
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
