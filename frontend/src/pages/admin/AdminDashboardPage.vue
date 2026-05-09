<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import ChartPlaceholder from '../../components/charts/ChartPlaceholder.vue'
import StatCard from '../../components/ui/StatCard.vue'
import { adminNavigation } from '../../data/navigation'
import { adminDashboardStats } from '../../data/sampleData'
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    user-name="Dr. Camille Navarro"
    context="Administrator Dashboard"
    role-label="Scholarship Officer"
    :notification-count="8"
  >
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <StatCard
        v-for="stat in adminDashboardStats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :tone="stat.tone"
      />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
      <ChartPlaceholder title="Applicant Trends" subtitle="Monthly application volume" variant="bars" />
      <ChartPlaceholder title="Approval Rate" subtitle="Approved versus reviewed" variant="donut" />
      <ChartPlaceholder title="Compliance Overview" subtitle="Scholar monitoring snapshot" variant="line" />
    </section>
  </DashboardLayout>
</template>
