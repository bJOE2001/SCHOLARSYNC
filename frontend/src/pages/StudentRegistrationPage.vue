<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import FormInput from '../components/forms/FormInput.vue'
import { api } from '../services/api'

const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)
const errorMessage = ref('')

const form = reactive({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  program: '',
  yearLevel: '',
  password: '',
  confirmPassword: '',
})

const yearLevelOptions = [
  { label: '1st Year', value: '1st Year' },
  { label: '2nd Year', value: '2nd Year' },
  { label: '3rd Year', value: '3rd Year' },
  { label: '4th Year', value: '4th Year' },
]

async function submitRegistration() {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await api.register(form)
    router.push(route.query.redirect
      ? {
          path: String(route.query.redirect),
          query: {
            program: route.query.program,
          },
        }
      : '/student/dashboard')
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="min-h-screen bg-slate-50 px-4 py-10">
    <section class="mx-auto max-w-4xl rounded-md border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
      <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Student Registration</p>
          <h1 class="mt-2 text-2xl font-bold text-slate-950">Create your applicant account</h1>
          <p class="mt-2 text-sm text-slate-500">Use your active contact information for application updates.</p>
        </div>
        <RouterLink to="/login" class="inline-flex justify-center rounded-md border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
          Back to Login
        </RouterLink>
      </div>

      <form class="mt-8 grid gap-5 md:grid-cols-2" @submit.prevent="submitRegistration">
        <FormInput id="first-name" v-model="form.firstName" label="First Name" placeholder="Alyssa" />
        <FormInput id="last-name" v-model="form.lastName" label="Last Name" placeholder="Mendoza" />
        <FormInput id="email" v-model="form.email" label="Email" type="email" placeholder="alyssa@college.edu" />
        <FormInput id="phone" v-model="form.phone" label="Phone Number" placeholder="0917 000 0000" />
        <FormInput id="program" v-model="form.program" label="Program of Study" placeholder="Example: BS Information Technology" />
        <FormInput id="year-level" v-model="form.yearLevel" label="Year Level" :options="yearLevelOptions" />
        <FormInput id="password" v-model="form.password" label="Password" type="password" placeholder="Create a password" />
        <FormInput id="confirm-password" v-model="form.confirmPassword" label="Confirm Password" type="password" placeholder="Confirm your password" />

        <p v-if="errorMessage" class="rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 md:col-span-2">
          {{ errorMessage }}
        </p>

        <div class="md:col-span-2">
          <button
            type="submit"
            class="w-full rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-slate-300 sm:w-auto"
            :disabled="isSubmitting"
          >
            {{ isSubmitting ? 'Submitting...' : 'Submit Registration' }}
          </button>
        </div>
      </form>
    </section>
  </main>
</template>
