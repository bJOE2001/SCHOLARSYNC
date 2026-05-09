<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import FormInput from '../components/forms/FormInput.vue'

const router = useRouter()
const email = ref('')
const password = ref('')
const role = ref('student')

const roleOptions = [
  { label: 'Student', value: 'student' },
  { label: 'Administrator', value: 'administrator' },
]

const dashboardPath = computed(() => (role.value === 'administrator' ? '/admin/dashboard' : '/student/dashboard'))

function submitLogin() {
  router.push(dashboardPath.value)
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

        <button
          type="submit"
          class="w-full rounded-md bg-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800"
        >
          Login
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">
        New applicant?
        <RouterLink to="/register" class="font-bold text-indigo-700 hover:text-indigo-800">
          Create an account
        </RouterLink>
      </p>
    </section>
  </main>
</template>
