<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import StatCard from '../../components/ui/StatCard.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { studentNavigation } from '../../data/navigation'
import {
  progressSteps,
  recentNotifications,
  studentDashboardStats,
  studentProfile,
} from '../../data/sampleData'
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Student Dashboard"
    role-label="Applicant"
    :notification-count="3"
  >
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <StatCard
        v-for="stat in studentDashboardStats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :tone="stat.tone"
      />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
      <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Application Progress</p>
            <h2 class="mt-2 text-xl font-bold text-slate-950">City Academic Excellence Grant</h2>
          </div>
          <StatusBadge status="Under Review" />
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div
            v-for="(step, index) in progressSteps"
            :key="step"
            class="relative rounded-md border p-5"
            :class="index <= 1 ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-slate-50'"
          >
            <div
              class="grid h-10 w-10 place-items-center rounded-md text-sm font-black"
              :class="index <= 1 ? 'bg-indigo-700 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200'"
            >
              {{ index + 1 }}
            </div>
            <p class="mt-4 text-sm font-bold text-slate-950">{{ step }}</p>
            <p class="mt-2 text-sm text-slate-500">
              {{ index === 0 ? 'Application form received.' : index === 1 ? 'Officer verification ongoing.' : 'Final decision pending.' }}
            </p>
          </div>
        </div>
      </article>

      <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Recent Notifications</p>
            <h2 class="mt-2 text-xl font-bold text-slate-950">Updates</h2>
          </div>
          <RouterLink to="/student/announcements" class="text-sm font-bold text-indigo-700 hover:text-indigo-800">
            View all
          </RouterLink>
        </div>

        <div class="mt-6 space-y-4">
          <div v-for="notification in recentNotifications" :key="notification.title" class="rounded-md bg-slate-50 p-4">
            <p class="text-sm font-bold text-slate-950">{{ notification.title }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ notification.message }}</p>
            <p class="mt-3 text-xs font-semibold text-slate-400">{{ notification.time }}</p>
          </div>
        </div>
      </article>
    </section>
  </DashboardLayout>
</template>
