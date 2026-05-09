<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import heroImage from '../assets/graduation-hero.png'
import StatusBadge from '../components/ui/StatusBadge.vue'
import { api, getCurrentUser } from '../services/api'

const scholarships = ref([])
const loading = ref(true)
const errorMessage = ref('')
const selectedScholarship = ref(null)
const showAllPrograms = ref(false)
const currentUser = getCurrentUser()

const openScholarships = computed(() => scholarships.value.filter((scholarship) => scholarship.status === 'Open'))
const featuredScholarships = computed(() => openScholarships.value.length ? openScholarships.value : scholarships.value)
const displayedScholarships = computed(() => showAllPrograms.value ? featuredScholarships.value : featuredScholarships.value.slice(0, 6))
const scholarshipCount = computed(() => openScholarships.value.length)

function applicationLink(scholarship) {
  const query = { program: scholarship.scholarshipName }

  if (currentUser?.role === 'student') {
    return {
      path: '/student/application',
      query,
    }
  }

  return {
    path: '/login',
    query: {
      redirect: '/student/application',
      program: scholarship.scholarshipName,
    },
  }
}

function openDetails(scholarship) {
  selectedScholarship.value = scholarship
}

function closeDetails() {
  selectedScholarship.value = null
}

function toggleAllPrograms() {
  showAllPrograms.value = !showAllPrograms.value
}

async function loadScholarships() {
  loading.value = true
  errorMessage.value = ''

  try {
    scholarships.value = await api.listScholarships({ status: 'Open' })
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadScholarships)
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-950">
    <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <span class="grid h-11 w-11 place-items-center rounded-md bg-indigo-700 text-sm font-bold text-white">SS</span>
          <span>
            <span class="block text-lg font-bold leading-tight">Scholarship Programs</span>
            <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">College Scholarship Office</span>
          </span>
        </RouterLink>

        <nav class="flex items-center gap-3">
          <RouterLink to="/how-to-apply" class="hidden rounded-md px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 md:inline-flex">
            How to Apply
          </RouterLink>
          <RouterLink to="/login" class="hidden rounded-md px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 sm:inline-flex">
            Login
          </RouterLink>
          <RouterLink to="/register" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800">
            Create Account
          </RouterLink>
        </nav>
      </div>
    </header>

    <main>
      <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        <img :src="heroImage" alt="" class="absolute inset-0 h-full w-full object-cover object-center opacity-45" />
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(15,23,42,0.92)_0%,rgba(15,23,42,0.78)_48%,rgba(15,23,42,0.58)_100%)]" aria-hidden="true"></div>

        <div class="relative mx-auto grid min-h-[520px] max-w-7xl content-center px-4 py-16 sm:px-6 lg:px-8">
          <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.24em] text-blue-200">
              College Scholarship Office
            </p>
            <h1 class="mt-5 text-5xl font-black tracking-normal text-white sm:text-6xl lg:text-7xl">
              Scholarship Programs
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-200 md:text-lg">
              Review currently available scholarship grants, eligibility requirements, required documents, application deadlines, and program contact information.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
              <a href="#scholarship-programs" class="inline-flex justify-center rounded-md bg-white px-6 py-3 text-sm font-bold text-indigo-800 shadow-lg shadow-black/20 transition hover:bg-blue-50">
                View Programs
              </a>
              <RouterLink to="/how-to-apply" class="inline-flex justify-center rounded-md border border-white/30 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                How to Apply
              </RouterLink>
            </div>

            <div class="mt-9 grid gap-4 sm:grid-cols-3">
              <div class="rounded-md border border-white/15 bg-white/10 p-4">
                <p class="text-3xl font-black">{{ scholarshipCount }}</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-blue-100">Open Programs</p>
              </div>
              <div class="rounded-md border border-white/15 bg-white/10 p-4">
                <p class="text-3xl font-black">Online</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-blue-100">Application Access</p>
              </div>
              <div class="rounded-md border border-white/15 bg-white/10 p-4">
                <p class="text-3xl font-black">Verified</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-blue-100">Program Details</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="scholarship-programs" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
          <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Available Scholarships</p>
            <h2 class="mt-3 text-3xl font-bold text-slate-950">Open programs for qualified students</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
              Select a scholarship program to review its qualifications and required documents before submitting an application.
            </p>
          </div>
        </div>

        <section v-if="errorMessage" class="mt-6 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
          {{ errorMessage }}
        </section>

        <section v-if="loading" class="mt-8 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
          Loading scholarship programs...
        </section>

        <div v-else-if="featuredScholarships.length" class="mt-8">
          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="Scholarship program list">
            <article
              v-for="scholarship in displayedScholarships"
              :key="scholarship.id"
              class="flex min-h-[300px] flex-col rounded-md border border-slate-200 bg-white p-5 shadow-sm"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">{{ scholarship.scholarshipType || 'Scholarship Program' }}</p>
                  <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-6 text-slate-950">{{ scholarship.scholarshipName }}</h3>
                </div>
                <StatusBadge :status="scholarship.status" />
              </div>

              <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">
                {{ scholarship.description || scholarship.announcementDetails || 'Program details are available from the scholarship office.' }}
              </p>

              <dl class="mt-4 grid gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2">
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Deadline</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.deadline || 'To be announced' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Slots</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.availableSlots || '0' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.academicYear || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">GPA</dt>
                  <dd class="mt-1 text-sm font-semibold text-slate-950">{{ scholarship.minimumGpa || 'Not specified' }}</dd>
                </div>
              </dl>

              <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row">
                <RouterLink :to="applicationLink(scholarship)" class="inline-flex justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-800">
                  Apply
                </RouterLink>
                <button type="button" class="inline-flex justify-center rounded-md border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="openDetails(scholarship)">
                  View Details
                </button>
              </div>
            </article>
          </div>

          <div v-if="featuredScholarships.length > 6" class="mt-4 flex justify-end">
            <button type="button" class="text-sm font-bold text-indigo-700 hover:text-indigo-800" @click="toggleAllPrograms">
              {{ showAllPrograms ? 'Show fewer programs' : 'View all programs' }}
            </button>
          </div>

        </div>

        <section v-else class="mt-8 rounded-md border border-slate-200 bg-white p-8 text-center shadow-sm">
          <h3 class="text-xl font-bold text-slate-950">No open scholarship programs</h3>
          <p class="mt-2 text-sm leading-6 text-slate-500">Please check back for new scholarship announcements from the College Scholarship Office.</p>
        </section>
      </section>
    </main>

    <Teleport to="body">
      <div
        v-if="selectedScholarship"
        class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4"
        aria-modal="true"
        role="dialog"
        aria-labelledby="scholarship-details-title"
        @click.self="closeDetails"
      >
        <section class="w-full max-w-3xl overflow-hidden rounded-md bg-white shadow-xl">
          <header class="flex items-start justify-between gap-4 border-b border-slate-200 p-6">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">{{ selectedScholarship.scholarshipType || 'Scholarship Program' }}</p>
              <h2 id="scholarship-details-title" class="mt-2 text-2xl font-bold text-slate-950">{{ selectedScholarship.scholarshipName }}</h2>
              <p class="mt-2 text-sm text-slate-500">{{ selectedScholarship.academicYear || 'Academic year not specified' }} · {{ selectedScholarship.semester || 'Semester not specified' }}</p>
            </div>
            <button type="button" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeDetails">
              Close
            </button>
          </header>

          <div class="max-h-[72vh] overflow-y-auto p-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div class="rounded-md bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status</p>
                <div class="mt-2"><StatusBadge :status="selectedScholarship.status" /></div>
              </div>
              <div class="rounded-md bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Deadline</p>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.deadline || 'To be announced' }}</p>
              </div>
              <div class="rounded-md bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Slots</p>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.availableSlots || '0' }}</p>
              </div>
              <div class="rounded-md bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Minimum GPA</p>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ selectedScholarship.minimumGpa || 'Not specified' }}</p>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <section>
                <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Program Description</h3>
                <p class="mt-3 text-sm leading-7 text-slate-700">{{ selectedScholarship.description || 'No description provided.' }}</p>
              </section>
              <section>
                <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Eligibility Requirements</h3>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ selectedScholarship.eligibilityRequirements || 'No eligibility requirements provided.' }}</p>
              </section>
              <section>
                <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Required Documents</h3>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ selectedScholarship.requiredDocuments || 'No required documents provided.' }}</p>
              </section>
              <section>
                <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Allowed Students</h3>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                  <div>
                    <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Year Level</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-950">{{ selectedScholarship.yearLevelAllowed || 'Not specified' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Program/Course</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-950">{{ selectedScholarship.programAllowed || 'Not specified' }}</dd>
                  </div>
                </dl>
              </section>
              <section>
                <h3 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500">Contact Person</h3>
                <p class="mt-3 text-sm leading-7 text-slate-700">{{ selectedScholarship.contactPerson || 'Scholarship Office' }}</p>
              </section>
            </div>
          </div>

          <footer class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 sm:flex-row sm:justify-end">
            <button type="button" class="rounded-md border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="closeDetails">
              Close
            </button>
            <RouterLink :to="applicationLink(selectedScholarship)" class="inline-flex justify-center rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
              Apply for this Program
            </RouterLink>
          </footer>
        </section>
      </div>
    </Teleport>
  </div>
</template>
