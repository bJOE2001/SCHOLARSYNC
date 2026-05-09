<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import FormInput from '../components/forms/FormInput.vue'
import { api } from '../services/api'

const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const role = ref('student')
const isSubmitting = ref(false)
const errorMessage = ref('')

const roleOptions = [
  { label: 'Student', value: 'student' },
  { label: 'Administrator', value: 'administrator' },
]

const dashboardPath = computed(() => (role.value === 'administrator' ? '/admin/dashboard' : '/student/dashboard'))
const registrationLink = computed(() => ({
  path: '/register',
  query: {
    redirect: route.query.redirect,
    program: route.query.program,
  },
}))

const postLoginRoute = computed(() => {
  if (role.value !== 'student' || !route.query.redirect) {
    return dashboardPath.value
  }

  return {
    path: String(route.query.redirect),
    query: {
      program: route.query.program,
    },
  }
})

async function submitLogin() {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await api.login({
      email: email.value,
      password: password.value,
      role: role.value,
    })

    router.push(postLoginRoute.value)
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="grid min-h-screen place-items-center bg-slate-50 px-4 py-10">
    <section class="w-full max-w-md rounded-md border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
      <RouterLink to="/" class="mx-auto grid h-14 w-14 place-items-center rounded-md bg-indigo-700 text-base font-black text-white">
        SS
      </RouterLink>
      <div class="mt-6 text-center">
        <h1 class="text-2xl font-bold text-slate-950">Welcome back</h1>
        <p class="mt-2 text-sm text-slate-500">Login to continue to your ScholarSync workspace.</p>
      </div>

      <form class="mt-8 space-y-5" @submit.prevent="submitLogin">
        <FormInput
          id="login-email"
          v-model="email"
          label="Email or Username"
          placeholder="student@college.edu"
        />
        <FormInput
          id="login-password"
          v-model="password"
          label="Password"
          type="password"
          placeholder="Enter your password"
        />
        <FormInput
          id="login-role"
          v-model="role"
          label="Role"
          :options="roleOptions"
        />

        <p v-if="errorMessage" class="rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
          {{ errorMessage }}
        </p>

        <button
          type="submit"
          class="w-full rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">
        New applicant?
        <RouterLink :to="registrationLink" class="font-bold text-indigo-700 hover:text-indigo-800">
          Create an account
        </RouterLink>
      </p>
    </section>
  </main>
</template>
