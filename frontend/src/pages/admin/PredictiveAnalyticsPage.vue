<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import ApexCharts from 'apexcharts'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatCard from '../../components/ui/StatCard.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'gpaTrend', label: 'GPA Trend' },
  { key: 'complianceScore', label: 'Compliance Score' },
  { key: 'riskScore', label: 'Risk Score' },
  { key: 'forecastLabel', label: 'Forecast Label' },
]

const selectedMetric = ref('applicants')
const selectedForecastIndex = ref(5)
const selectedRiskLevel = ref('Medium')
const forecastChartElement = ref(null)
const riskChartElement = ref(null)
const analyticsCards = ref([])
const predictedAtRiskScholars = ref([])
const applicantForecast = ref([])
const riskOverview = ref([])
const loading = ref(true)
const errorMessage = ref('')
const currentUser = getCurrentUser()
let forecastChart = null
let riskChart = null

const metricOptions = [
  { label: 'Applicants', value: 'applicants' },
  { label: 'Approvals', value: 'approvals' },
  { label: 'Probability', value: 'probability' },
]

const selectedForecast = computed(() => {
  return applicantForecast.value[selectedForecastIndex.value] ?? {
    month: 'N/A',
    applicants: 0,
    approvals: 0,
    probability: 0,
  }
})

const metricLabel = computed(() => {
  return metricOptions.find((metric) => metric.value === selectedMetric.value)?.label ?? 'Applicants'
})

const maxForecastValue = computed(() => {
  return Math.max(...applicantForecast.value.map((item) => item[selectedMetric.value]), 0)
})

const selectedRisk = computed(() => {
  return riskOverview.value.find((risk) => risk.level === selectedRiskLevel.value) ?? riskOverview.value[0]
})

const selectedRiskIndex = computed(() => {
  return riskOverview.value.findIndex((risk) => risk.level === selectedRiskLevel.value)
})

function metricValues() {
  return applicantForecast.value.map((item) => item[selectedMetric.value])
}

function metricValueFormatter(value) {
  if (selectedMetric.value === 'probability') {
    return `${value}%`
  }

  return Number(value).toLocaleString()
}

function forecastColors() {
  return applicantForecast.value.map((_, index) => (index === selectedForecastIndex.value ? '#4338ca' : '#93c5fd'))
}

function forecastChartOptions() {
  return {
    chart: {
      type: 'bar',
      height: 285,
      fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
      toolbar: { show: false },
      events: {
        dataPointSelection(_, __, config) {
          selectedForecastIndex.value = config.dataPointIndex
        },
      },
    },
    series: [
      {
        name: metricLabel.value,
        data: metricValues(),
      },
    ],
    colors: forecastColors(),
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '46%',
        distributed: true,
      },
    },
    dataLabels: { enabled: false },
    legend: { show: false },
    grid: {
      borderColor: '#e2e8f0',
      strokeDashArray: 4,
      padding: { left: 8, right: 8 },
    },
    xaxis: {
      categories: applicantForecast.value.map((item) => item.month),
      labels: {
        style: {
          colors: '#64748b',
          fontWeight: 700,
        },
      },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: {
        formatter: metricValueFormatter,
        style: {
          colors: '#64748b',
        },
      },
    },
    tooltip: {
      y: {
        formatter: metricValueFormatter,
      },
    },
    states: {
      hover: {
        filter: { type: 'darken', value: 0.85 },
      },
      active: {
        filter: { type: 'none' },
      },
    },
  }
}

function riskChartOptions() {
  return {
    chart: {
      type: 'donut',
      height: 285,
      fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
      toolbar: { show: false },
      events: {
        dataPointSelection(_, __, config) {
          selectedRiskLevel.value = riskOverview.value[config.dataPointIndex]?.level ?? selectedRiskLevel.value
        },
      },
    },
    series: riskOverview.value.map((risk) => risk.count),
    labels: riskOverview.value.map((risk) => `${risk.level} Risk`),
    colors: riskOverview.value.map((risk) => risk.color),
    stroke: {
      colors: ['#ffffff'],
      width: 4,
    },
    legend: {
      show: false,
    },
    dataLabels: {
      enabled: true,
      formatter(value) {
        return `${Math.round(value)}%`
      },
      style: {
        fontSize: '12px',
        fontWeight: 700,
      },
    },
    plotOptions: {
      pie: {
        donut: {
          size: '68%',
          labels: {
            show: true,
            name: {
              show: true,
              color: '#64748b',
              fontSize: '12px',
              fontWeight: 700,
            },
            value: {
              show: true,
              color: '#0f172a',
              fontSize: '22px',
              fontWeight: 900,
              formatter(value) {
                return value
              },
            },
            total: {
              show: true,
              label: 'Total',
              color: '#64748b',
              fontSize: '12px',
              formatter() {
                return riskOverview.value.reduce((sum, risk) => sum + risk.count, 0)
              },
            },
          },
        },
      },
    },
    tooltip: {
      y: {
        formatter(value) {
          return `${value} scholars`
        },
      },
    },
  }
}

function riskDescriptions(items) {
  const total = items.reduce((sum, risk) => sum + risk.count, 0) || 1

  return items.map((risk) => ({
    ...risk,
    percent: Math.round((risk.count / total) * 100),
    description: risk.level === 'Low'
      ? 'Scholars with stable grades and complete compliance records.'
      : risk.level === 'Medium'
        ? 'Scholars needing monitoring due to partial compliance or grade movement.'
        : 'Scholars recommended for intervention and close follow-up.',
  }))
}

async function loadAnalytics() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.getAnalytics()
    analyticsCards.value = response.cards ?? []
    applicantForecast.value = response.applicantForecast ?? []
    riskOverview.value = riskDescriptions(response.riskOverview ?? [])
    predictedAtRiskScholars.value = response.predictedAtRiskScholars?.data ?? response.predictedAtRiskScholars ?? []
    selectedForecastIndex.value = Math.max(applicantForecast.value.length - 1, 0)

    forecastChart?.destroy()
    riskChart?.destroy()

    if (forecastChartElement.value) {
      forecastChart = new ApexCharts(forecastChartElement.value, forecastChartOptions())
      forecastChart.render()
    }

    if (riskChartElement.value) {
      riskChart = new ApexCharts(riskChartElement.value, riskChartOptions())
      riskChart.render()
    }
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

function updateForecastChart() {
  if (!forecastChart) {
    return
  }

  forecastChart.updateOptions({
    series: [
      {
        name: metricLabel.value,
        data: metricValues(),
      },
    ],
    colors: forecastColors(),
    yaxis: {
      labels: {
        formatter: metricValueFormatter,
        style: { colors: '#64748b' },
      },
    },
    tooltip: {
      y: {
        formatter: metricValueFormatter,
      },
    },
  })
}

onMounted(loadAnalytics)

watch([selectedMetric, selectedForecastIndex], updateForecastChart)

onBeforeUnmount(() => {
  forecastChart?.destroy()
  riskChart?.destroy()
})
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Predictive Analytics"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="loading" class="mb-5 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading analytics...
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <StatCard
        v-for="card in analyticsCards"
        :key="card.title"
        :title="card.title"
        :value="card.value"
        :subtitle="card.subtitle"
        :tone="card.tone"
      />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
      <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h3 class="text-base font-bold text-slate-950">Applicant Forecast</h3>
            <p class="mt-1 text-sm text-slate-500">Click a month or change the metric to inspect projections.</p>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              v-for="metric in metricOptions"
              :key="metric.value"
              type="button"
              class="rounded-md px-3 py-2 text-xs font-bold transition"
              :class="selectedMetric === metric.value ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
              @click="selectedMetric = metric.value"
            >
              {{ metric.label }}
            </button>
          </div>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_180px]">
          <div class="rounded-md bg-slate-50 p-3">
            <div ref="forecastChartElement" class="min-h-72"></div>
          </div>

          <div class="rounded-md border border-slate-200 p-4">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Selected Month</p>
            <h4 class="mt-2 text-2xl font-bold text-slate-950">{{ selectedForecast.month }}</h4>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">Applicants</dt>
                <dd class="font-bold text-slate-950">{{ selectedForecast.applicants.toLocaleString() }}</dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">Approvals</dt>
                <dd class="font-bold text-slate-950">{{ selectedForecast.approvals.toLocaleString() }}</dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">Probability</dt>
                <dd class="font-bold text-slate-950">{{ selectedForecast.probability }}%</dd>
              </div>
            </dl>
            <p class="mt-4 rounded-md bg-indigo-50 p-3 text-xs font-semibold leading-5 text-indigo-800">
              Viewing {{ metricLabel.toLowerCase() }} forecast for {{ selectedForecast.month }}.
            </p>
          </div>
        </div>
      </article>

      <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h3 class="text-base font-bold text-slate-950">Compliance Risk Overview</h3>
            <p class="mt-1 text-sm text-slate-500">Click a risk group to review projected scholar count.</p>
          </div>
          <span class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">Interactive</span>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-[220px_1fr]">
          <div class="rounded-md bg-slate-50 p-3">
            <div ref="riskChartElement" class="min-h-72"></div>
          </div>

          <div class="space-y-3">
            <button
              v-for="risk in riskOverview"
              :key="risk.level"
              type="button"
              class="w-full rounded-md border p-4 text-left transition"
              :class="selectedRiskLevel === risk.level ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200 hover:bg-slate-50'"
              @click="selectedRiskLevel = risk.level"
            >
              <div class="flex items-center justify-between gap-4">
                <span class="flex items-center gap-3">
                  <span class="h-3 w-3 rounded-md" :style="{ backgroundColor: risk.color }"></span>
                  <span class="text-sm font-bold text-slate-950">{{ risk.level }} Risk</span>
                </span>
                <span class="text-sm font-black text-slate-950">{{ risk.count }}</span>
              </div>
              <div class="mt-3 h-2 overflow-hidden rounded-md bg-slate-200">
                <div class="h-full rounded-md" :style="{ width: `${risk.percent}%`, backgroundColor: risk.color }"></div>
              </div>
              <p class="mt-3 text-xs leading-5 text-slate-500">{{ risk.description }}</p>
            </button>
          </div>
        </div>
      </article>
    </section>

    <section class="mt-6">
      <div class="mb-4">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-700">Predicted At-Risk Scholars</p>
        <h2 class="mt-2 text-xl font-bold text-slate-950">Forecast table</h2>
      </div>
      <DataTable :columns="columns" :rows="predictedAtRiskScholars">
        <template #cell-forecastLabel="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
      </DataTable>
    </section>
  </DashboardLayout>
</template>
