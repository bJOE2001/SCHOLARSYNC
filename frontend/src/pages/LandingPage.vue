<script setup>
import { RouterLink } from 'vue-router'
import heroImage from '../assets/hero.png'
import { featureCards } from '../data/sampleData'
</script>

<template>
  <div class="min-h-screen bg-white text-slate-950">
    <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <span class="grid h-11 w-11 place-items-center rounded-md bg-indigo-700 text-sm font-bold text-white">SS</span>
          <span>
            <span class="block text-lg font-bold leading-tight">ScholarSync</span>
            <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Scholarship System</span>
          </span>
        </RouterLink>

        <nav class="flex items-center gap-3">
          <RouterLink to="/login" class="hidden rounded-md px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 sm:inline-flex">
            Login
          </RouterLink>
          <RouterLink to="/register" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800">
            Apply Now
          </RouterLink>
        </nav>
      </div>
    </header>

    <main>
      <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 bg-indigo-950" aria-hidden="true"></div>
        <div class="absolute inset-y-0 right-0 hidden w-1/2 opacity-25 lg:block" aria-hidden="true">
          <img :src="heroImage" alt="" class="h-full w-full object-contain object-center p-16" />
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,#0f172a_0%,rgba(15,23,42,0.92)_48%,rgba(30,41,59,0.78)_100%)]" aria-hidden="true"></div>

        <div class="relative mx-auto grid min-h-[620px] max-w-7xl content-center px-4 py-16 sm:px-6 lg:px-8">
          <div class="max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.24em] text-blue-200">
              Integrated Scholarship Management and Monitoring System
            </p>
            <h1 class="mt-5 text-5xl font-black tracking-normal text-white sm:text-6xl lg:text-7xl">
              ScholarSync
            </h1>
            <p class="mt-5 max-w-2xl text-xl font-semibold text-blue-100">
              Integrated Scholarship Management and Monitoring System
            </p>
            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-200 md:text-lg">
              Apply for scholarship programs, upload requirements, track application progress, receive announcements, and help scholarship officers manage applicants and scholars online.
            </p>

            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
              <RouterLink to="/register" class="inline-flex justify-center rounded-md bg-white px-6 py-3 text-sm font-bold text-indigo-800 shadow-lg shadow-black/20 transition hover:bg-blue-50">
                Apply Now
              </RouterLink>
              <RouterLink to="/login" class="inline-flex justify-center rounded-md border border-white/30 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                Login
              </RouterLink>
            </div>
          </div>
        </div>
      </section>

      <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Platform Features</p>
          <h2 class="mt-3 text-3xl font-bold text-slate-950">A cleaner workflow for applicants and scholarship officers.</h2>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="feature in featureCards"
            :key="feature.title"
            class="rounded-md border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
          >
            <div class="mb-5 grid h-12 w-12 place-items-center rounded-md bg-indigo-50 text-sm font-black text-indigo-700">
              {{ feature.title.slice(0, 2).toUpperCase() }}
            </div>
            <h3 class="text-lg font-bold text-slate-950">{{ feature.title }}</h3>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ feature.description }}</p>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>
