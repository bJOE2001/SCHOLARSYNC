<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-950">
    <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <span class="grid h-11 w-11 place-items-center rounded-md bg-indigo-700 text-sm font-bold text-white">SS</span>
          <span>
            <span class="block text-lg font-bold leading-tight">How to Apply</span>
            <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">College Scholarship Office</span>
          </span>
        </RouterLink>

        <nav class="flex items-center gap-3">
          <RouterLink to="/" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100">
            Programs
          </RouterLink>
          <RouterLink to="/login" class="rounded-md bg-indigo-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800">
            Applicant Login
          </RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <section class="grid gap-8 lg:grid-cols-[1fr_0.8fr] lg:items-start">
        <div>
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Application Tutorial</p>
          <h1 class="mt-3 text-4xl font-black tracking-normal text-slate-950 sm:text-5xl">
            How to Apply for a Scholarship
          </h1>
          <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
            Follow these steps before submitting your application. A tutorial video will be added here once the final guide is available.
          </p>
        </div>

        <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
          <div class="grid aspect-video place-items-center rounded-md border border-dashed border-slate-300 bg-slate-100">
            <div class="text-center">
              <div class="mx-auto grid h-16 w-16 place-items-center rounded-md bg-indigo-700 text-white">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M8 5.4v13.2L18.5 12 8 5.4Z" />
                </svg>
              </div>
              <p class="mt-4 text-sm font-bold text-slate-950">Tutorial video placeholder</p>
              <p class="mt-2 text-xs font-semibold text-slate-500">The official video guide will be inserted here later.</p>
            </div>
          </div>
        </section>
      </section>

      <section class="mt-10 grid gap-5 lg:grid-cols-4">
        <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-black text-indigo-700">01</p>
          <h2 class="mt-3 text-lg font-bold text-slate-950">Choose a Program</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">Review open scholarship programs, requirements, deadlines, available slots, and contact information.</p>
        </article>
        <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-black text-indigo-700">02</p>
          <h2 class="mt-3 text-lg font-bold text-slate-950">Create an Account</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">Register with your active student information so the office can validate your application records.</p>
        </article>
        <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-black text-indigo-700">03</p>
          <h2 class="mt-3 text-lg font-bold text-slate-950">Submit Application</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">Complete the application form, confirm your information, and submit your scholarship statement.</p>
        </article>
        <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-black text-indigo-700">04</p>
          <h2 class="mt-3 text-lg font-bold text-slate-950">Upload Documents</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">Upload clear copies of the required documents and monitor your application status online.</p>
        </article>
      </section>

      <section class="mt-10 rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Ready to Start</p>
            <h2 class="mt-2 text-xl font-bold text-slate-950">Review open programs before applying.</h2>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row">
            <RouterLink to="/" class="inline-flex justify-center rounded-md border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
              View Programs
            </RouterLink>
            <RouterLink to="/register" class="inline-flex justify-center rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-800">
              Create Account
            </RouterLink>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
