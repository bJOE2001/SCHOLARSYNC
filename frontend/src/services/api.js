import { reactive } from 'vue'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api'
const SESSION_KEY = 'scholarsync.session'
const API_LOADING_EVENT = 'scholarsync:api-loading'
let activeApiRequests = 0

export const apiLoadingState = reactive({
  active: false,
  count: 0,
})

function notifyApiLoading() {
  apiLoadingState.active = activeApiRequests > 0
  apiLoadingState.count = activeApiRequests

  if (typeof window === 'undefined') {
    return
  }

  window.dispatchEvent(new CustomEvent(API_LOADING_EVENT, {
    detail: {
      active: apiLoadingState.active,
      count: apiLoadingState.count,
    },
  }))
}

function startApiLoading() {
  activeApiRequests += 1
  notifyApiLoading()
}

function stopApiLoading() {
  activeApiRequests = Math.max(activeApiRequests - 1, 0)
  notifyApiLoading()
}

function buildQuery(params = {}) {
  const query = new URLSearchParams()

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.set(key, value)
    }
  })

  const queryString = query.toString()

  return queryString ? `?${queryString}` : ''
}

export function getSession() {
  try {
    return JSON.parse(localStorage.getItem(SESSION_KEY)) ?? null
  } catch {
    return null
  }
}

export function getCurrentUser() {
  return getSession()?.user ?? null
}

export function saveSession(session) {
  localStorage.setItem(SESSION_KEY, JSON.stringify(session))
}

export function clearSession() {
  localStorage.removeItem(SESSION_KEY)
}

async function request(path, options = {}) {
  startApiLoading()

  const session = getSession()
  const headers = {
    Accept: 'application/json',
    ...(options.headers ?? {}),
  }

  const config = {
    ...options,
    headers,
  }

  if (session?.token) {
    headers.Authorization = `Bearer ${session.token}`
  }

  if (options.body && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    config.body = JSON.stringify(options.body)
  }

  try {
    const response = await fetch(`${API_BASE_URL}${path}`, config)
    const payload = response.status === 204 ? null : await response.json().catch(() => null)

    if (!response.ok) {
      const validationMessage = payload?.errors
        ? Object.values(payload.errors).flat().join(' ')
        : null

      throw new Error(validationMessage || payload?.message || 'The backend request failed.')
    }

    return payload
  } finally {
    stopApiLoading()
  }
}

function data(payload) {
  return payload?.data ?? payload
}

export const api = {
  async register(payload) {
    const response = await request('/auth/register', {
      method: 'POST',
      body: payload,
    })

    saveSession(response)

    return response
  },

  async login(payload) {
    const response = await request('/auth/login', {
      method: 'POST',
      body: payload,
    })

    saveSession(response)

    return response
  },

  async logout() {
    await request('/auth/logout', { method: 'POST' }).catch(() => null)
    clearSession()
  },

  async getStudentProfile(userId) {
    return data(await request(`/profile/student${buildQuery({ user_id: userId })}`))
  },

  async getStudentDashboard(userId) {
    return request(`/dashboard/student${buildQuery({ user_id: userId })}`)
  },

  async getAdminDashboard() {
    return request('/dashboard/admin')
  },

  async listNotifications(params = {}) {
    return data(await request(`/notifications${buildQuery(params)}`))
  },

  async listScholarships(params = {}) {
    return data(await request(`/scholarships${buildQuery(params)}`))
  },

  async createScholarship(payload) {
    return data(await request('/scholarships', {
      method: 'POST',
      body: payload,
    }))
  },

  async listApplications(params = {}) {
    return data(await request(`/applications${buildQuery(params)}`))
  },

  async getApplication(id) {
    return data(await request(`/applications/${id}`))
  },

  async createApplication(payload) {
    return data(await request('/applications', {
      method: 'POST',
      body: payload,
    }))
  },

  async updateApplicationStatus(id, payload) {
    return data(await request(`/applications/${id}/status`, {
      method: 'PATCH',
      body: payload,
    }))
  },

  async listDocuments(params = {}) {
    return data(await request(`/documents${buildQuery(params)}`))
  },

  async getDocument(id) {
    return data(await request(`/documents/${id}`))
  },

  async createDocument(payload) {
    return data(await request('/documents', {
      method: 'POST',
      body: payload,
    }))
  },

  async updateDocumentStatus(id, payload) {
    return data(await request(`/documents/${id}/status`, {
      method: 'PATCH',
      body: payload,
    }))
  },

  async listAnnouncements(params = {}) {
    return data(await request(`/announcements${buildQuery(params)}`))
  },

  async createAnnouncement(payload) {
    return data(await request('/announcements', {
      method: 'POST',
      body: payload,
    }))
  },

  async listComplianceRecords(params = {}) {
    return data(await request(`/compliance-records${buildQuery(params)}`))
  },

  async getAnalytics() {
    return request('/analytics')
  },

  async getApplicationReport(params = {}) {
    return request(`/reports/applications${buildQuery(params)}`)
  },
}
