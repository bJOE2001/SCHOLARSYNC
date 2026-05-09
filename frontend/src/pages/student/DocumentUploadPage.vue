<script setup>
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import UploadCard from '../../components/uploads/UploadCard.vue'
import { studentNavigation } from '../../data/navigation'
import { studentProfile, uploadDocuments } from '../../data/sampleData'
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Applicant Portal"
    :sidebar-items="studentNavigation"
    :user-name="studentProfile.name"
    context="Upload Documents"
    role-label="Applicant"
    :notification-count="3"
  >
    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Document Checklist</p>
      <h2 class="mt-2 text-xl font-bold text-slate-950">Upload scholarship requirements</h2>
      <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
        Upload clear and readable copies of your required documents. File status will update after administrator review.
      </p>
    </section>

    <section class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <UploadCard
        v-for="document in uploadDocuments"
        :key="document.documentType"
        :document-type="document.documentType"
        :file-name="document.fileName"
        :status="document.status"
      />
    </section>
  </DashboardLayout>
</template>
