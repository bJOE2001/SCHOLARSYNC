<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { studentNavigation } from '../../data/navigation'
import { applicationTimeline, studentProfile } from '../../data/sampleData'
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Application Status"
    role-label="Applicant"
    :notification-count="3"
  >
    <section class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
      <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Current Application</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ studentProfile.scholarshipProgram }}</h2>
          </div>
          <StatusBadge status="Under Review" />
        </div>

        <div class="mt-8 space-y-5">
          <div
            v-for="item in applicationTimeline"
            :key="item.title"
            class="relative rounded-md border border-slate-200 p-5"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 class="text-base font-bold text-slate-950">{{ item.title }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ item.detail }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-slate-500">{{ item.date }}</span>
                <StatusBadge :status="item.status" />
              </div>
            </div>
          </div>
        </div>
      </article>

      <aside class="space-y-6">
        <article class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Administrator Remarks</p>
          <p class="mt-4 text-sm leading-7 text-slate-600">
            Please upload a clearer copy of your valid ID. Other submitted documents are readable and currently under verification.
          </p>
        </article>

        <article class="rounded-md border border-indigo-100 bg-indigo-50 p-6">
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Reminder</p>
          <h3 class="mt-3 text-lg font-bold text-slate-950">Complete pending requirements</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">
            Submit document revisions before May 20, 2026 to keep your application moving.
          </p>
        </article>
      </aside>
    </section>
  </DashboardLayout>
</template>
