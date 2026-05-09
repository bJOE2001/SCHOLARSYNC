<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import ApexCharts from 'apexcharts'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  variant: {
    type: String,
    default: 'bars',
  },
})

const chartElement = ref(null)
let chart = null

function chartOptions() {
  const baseOptions = {
    chart: {
      height: 190,
      fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
      toolbar: { show: false },
    },
    colors: ['#4f46e5', '#3b82f6', '#e11d48'],
    grid: {
      borderColor: '#e2e8f0',
      strokeDashArray: 4,
    },
    legend: {
      show: false,
    },
    dataLabels: {
      enabled: false,
    },
  }

  if (props.variant === 'donut') {
    return {
      ...baseOptions,
      chart: {
        ...baseOptions.chart,
        type: 'donut',
      },
      series: [68, 22, 10],
      labels: ['Approved', 'Pending', 'At Risk'],
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Rate',
                formatter() {
                  return '68%'
                },
              },
            },
          },
        },
      },
      stroke: {
        colors: ['#ffffff'],
        width: 4,
      },
    }
  }

  if (props.variant === 'line') {
    return {
      ...baseOptions,
      chart: {
        ...baseOptions.chart,
        type: 'area',
      },
      series: [
        {
          name: 'Compliance',
          data: [72, 78, 74, 83, 88, 91],
        },
      ],
      stroke: {
        curve: 'smooth',
        width: 3,
      },
      fill: {
        type: 'gradient',
        gradient: {
          opacityFrom: 0.35,
          opacityTo: 0,
        },
      },
      xaxis: {
        categories: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
        labels: { style: { colors: '#64748b' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
      },
      yaxis: {
        labels: { style: { colors: '#64748b' } },
      },
    }
  }

  return {
    ...baseOptions,
    chart: {
      ...baseOptions.chart,
      type: 'bar',
    },
    series: [
      {
        name: 'Applicants',
        data: [980, 1120, 1080, 1310, 1240, 1520],
      },
    ],
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '45%',
      },
    },
    xaxis: {
      categories: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
      labels: { style: { colors: '#64748b' } },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: { style: { colors: '#64748b' } },
    },
  }
}

function renderChart() {
  if (!chartElement.value) {
    return
  }

  chart?.destroy()
  chart = new ApexCharts(chartElement.value, chartOptions())
  chart.render()
}

onMounted(renderChart)

watch(() => props.variant, renderChart)

onBeforeUnmount(() => {
  chart?.destroy()
})
</script>

<template>
  <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="text-base font-bold text-slate-950">{{ title }}</h3>
        <p v-if="subtitle" class="mt-1 text-sm text-slate-500">{{ subtitle }}</p>
      </div>
      <span class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">Flowbite</span>
    </div>

    <div class="mt-6 rounded-md bg-slate-50 p-3">
      <div ref="chartElement" class="min-h-48"></div>
    </div>
  </article>
</template>
