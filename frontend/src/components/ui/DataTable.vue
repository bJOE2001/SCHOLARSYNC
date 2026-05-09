<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  rows: {
    type: Array,
    required: true,
  },
  emptyText: {
    type: String,
    default: '',
  },
  rowKey: {
    type: String,
    default: '',
  },
  initialPerPage: {
    type: Number,
    default: 5,
  },
  perPageOptions: {
    type: Array,
    default: () => [5, 10, 15],
  },
})

const currentPage = ref(1)
const perPage = ref(props.initialPerPage)

const totalPages = computed(() => Math.max(1, Math.ceil(props.rows.length / perPage.value)))
const startIndex = computed(() => (currentPage.value - 1) * perPage.value)
const endIndex = computed(() => Math.min(startIndex.value + perPage.value, props.rows.length))
const paginatedRows = computed(() => props.rows.slice(startIndex.value, endIndex.value))

watch(
  () => [props.rows, perPage.value],
  () => {
    currentPage.value = 1
  },
)

watch(totalPages, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value
  }
})

function goToPage(page) {
  currentPage.value = Math.min(Math.max(page, 1), totalPages.value)
}

function resolveRowKey(row, index) {
  if (props.rowKey && row[props.rowKey]) {
    return row[props.rowKey]
  }

  return row.id || row.fileName || row.name || row.scholarName || row.applicantName || index
}
</script>

<template>
  <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="whitespace-nowrap px-5 py-4 text-xs font-bold uppercase tracking-[0.12em] text-slate-500"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="rows.length === 0">
            <td :colspan="columns.length" class="px-5 py-10 text-center text-slate-500">
              {{ emptyText || 'No records found.' }}
            </td>
          </tr>
          <tr v-for="(row, rowIndex) in paginatedRows" :key="resolveRowKey(row, rowIndex)" class="hover:bg-slate-50/80">
            <td
              v-for="column in columns"
              :key="column.key"
              class="whitespace-nowrap px-5 py-4 text-slate-700"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="row[column.key]"
              >
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
        <tfoot class="border-t border-slate-200 bg-slate-50">
          <tr>
            <td :colspan="columns.length" class="px-5 py-4">
              <div class="flex flex-col gap-2 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <span>
                  Showing <strong class="text-slate-950">{{ rows.length ? startIndex + 1 : 0 }}</strong>
                  to <strong class="text-slate-950">{{ endIndex }}</strong>
                  of <strong class="text-slate-950">{{ rows.length }}</strong> records
                </span>
                <slot
                  name="footer-summary"
                  :rows="rows"
                  :paginated-rows="paginatedRows"
                ></slot>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-wrap items-center gap-3">
        <p class="text-sm font-semibold text-slate-500">
          Page {{ currentPage }} of {{ totalPages }}
        </p>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-500">
          Rows
          <select
            v-model.number="perPage"
            class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          >
            <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </label>
      </div>

      <div class="flex flex-wrap gap-2">
        <button
          type="button"
          class="rounded-md border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          Previous
        </button>
        <button
          v-for="page in totalPages"
          :key="page"
          type="button"
          class="rounded-md px-3 py-2 text-sm font-bold"
          :class="page === currentPage ? 'bg-indigo-700 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
        <button
          type="button"
          class="rounded-md border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
