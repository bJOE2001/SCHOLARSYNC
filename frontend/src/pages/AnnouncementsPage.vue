<script setup>
import { computed, reactive } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import FormInput from '../components/forms/FormInput.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import { adminNavigation, studentNavigation } from '../data/navigation'
import { announcements, studentProfile } from '../data/sampleData'

const route = useRoute()
const isAdmin = computed(() => route.meta.role === 'admin')

const form = reactive({
  title: '',
  message: '',
  audience: '',
  date: '',
})

const audienceOptions = [
  { label: 'All Students', value: 'All Students' },
  { label: 'Applicants', value: 'Applicants' },
  { label: 'Approved Scholars', value: 'Approved Scholars' },
  { label: 'At-Risk Scholars', value: 'At-Risk Scholars' },
]

const layoutConfig = computed(() => {
  if (isAdmin.value) {
    return {
      sidebarTitle: 'ScholarSync',
      sidebarSubtitle: 'Officer Portal',
      sidebarItems: adminNavigation,
      userName: 'Dr. Camille Navarro',
      context: 'Announcements',
      roleLabel: 'Scholarship Officer',
      notificationCount: 8,
    }
  }

  return {
    sidebarTitle: 'ScholarSync',
    sidebarSubtitle: 'Applicant Portal',
    sidebarItems: studentNavigation,
    userName: studentProfile.name,
    context: 'Notifications',
    roleLabel: 'Applicant',
    notificationCount: 3,
  }
})
</script>

<template>
  <DashboardLayout
    :sidebar-title="layoutConfig.sidebarTitle"
    :sidebar-subtitle="layoutConfig.sidebarSubtitle"
    :sidebar-items="layoutConfig.sidebarItems"
    :user-name="layoutConfig.userName"
    :context="layoutConfig.context"
    :role-label="layoutConfig.roleLabel"
    :notification-count="layoutConfig.notificationCount"
  >
    <section v-if="isAdmin" class="mb-6 rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Create Announcement</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Publish an update</h2>

      <form class="mt-6 grid gap-5 md:grid-cols-2">
        <FormInput id="announcement-title" v-model="form.title" label="Title" placeholder="Scholarship renewal window" />
        <FormInput id="announcement-audience" v-model="form.audience" label="Audience" :options="audienceOptions" />
        <FormInput id="announcement-date" v-model="form.date" label="Date" type="date" />
        <div class="md:col-span-2">
          <FormInput
            id="announcement-message"
            v-model="form.message"
            label="Message"
            placeholder="Write announcement details here."
            textarea
            :rows="5"
          />
        </div>
        <div class="md:col-span-2">
          <button type="button" class="rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
            Publish Announcement
          </button>
        </div>
      </form>
    </section>

    <section>
      <div class="mb-4">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">
          {{ isAdmin ? 'Announcement List' : 'Latest Announcements' }}
        </p>
        <h2 class="mt-2 text-xl font-bold text-slate-950">
          {{ isAdmin ? 'Published updates' : 'Scholarship office updates' }}
        </h2>
      </div>

      <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
        <div
          v-for="announcement in announcements"
          :key="announcement.title"
          class="border-b border-slate-200 p-5 last:border-b-0 hover:bg-slate-50"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h3 class="text-lg font-bold text-slate-950">{{ announcement.title }}</h3>
              <p class="mt-2 text-sm font-semibold text-slate-500">{{ announcement.date }}</p>
            </div>
            <StatusBadge :status="announcement.audience" />
          </div>
          <p class="mt-4 text-sm leading-7 text-slate-600">{{ announcement.message }}</p>
        </div>
      </div>
    </section>
  </DashboardLayout>
</template>
