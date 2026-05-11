<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useSlots, watch } from 'vue'
import { useRouter } from 'vue-router'
import { DataTable as FlowbiteDataTable } from 'simple-datatables'

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
  searchable: {
    type: Boolean,
    default: true,
  },
  sortable: {
    type: Boolean,
    default: true,
  },
  searchPlaceholder: {
    type: String,
    default: 'Search records',
  },
})

const emit = defineEmits(['row-action'])

const router = useRouter()
const slots = useSlots()
const datatableHost = ref(null)
const sourceTable = ref(null)
const tableId = `datatable-${Math.random().toString(36).slice(2, 10)}`

let dataTable = null
let refreshPending = false

const hasFooterSummary = computed(() => Boolean(slots['footer-summary']))

const dataTableColumns = computed(() => props.columns.map((column, index) => ({
  select: index,
  sortable: isColumnSortable(column),
  searchable: isColumnSearchable(column),
  type: column.type || 'html',
})))

const paginationClasses = {
  active: 'datatable-active',
  disabled: 'datatable-disabled',
  paginationList: 'datatable-pagination-list',
  paginationListItem: 'datatable-pagination-list-item',
  paginationListItemLink: 'datatable-pagination-list-item-link',
}

watch(() => [
  props.rows,
  props.columns,
  props.emptyText,
  props.initialPerPage,
  props.perPageOptions,
  props.searchable,
  props.sortable,
  props.searchPlaceholder,
], scheduleDataTableRefresh, {
  flush: 'post',
})

onMounted(initDataTable)
onBeforeUnmount(destroyDataTable)

function resolveRowKey(row, index) {
  if (props.rowKey && row[props.rowKey] !== undefined && row[props.rowKey] !== null) {
    return row[props.rowKey]
  }

  return row.id ?? row.fileName ?? row.name ?? row.scholarName ?? row.applicantName ?? index
}

function rowKeyAsString(row, index) {
  return String(resolveRowKey(row, index))
}

function isColumnSortable(column) {
  return props.sortable && column.sortable !== false && column.key !== 'actions'
}

function isColumnSearchable(column) {
  return props.searchable && column.searchable !== false && column.key !== 'actions'
}

function destroyDataTable() {
  if (!dataTable) {
    datatableHost.value?.replaceChildren()
    return
  }

  dataTable.destroy()
  dataTable = null
  datatableHost.value?.replaceChildren()
}

function scheduleDataTableRefresh() {
  destroyDataTable()

  if (refreshPending) {
    return
  }

  refreshPending = true
  nextTick(() => {
    refreshPending = false
    initDataTable()
  })
}

function initDataTable() {
  if (!datatableHost.value || !sourceTable.value || dataTable) {
    return
  }

  const clonedTable = document.createElement('table')
  clonedTable.id = tableId
  clonedTable.innerHTML = sourceTable.value.innerHTML
  datatableHost.value.replaceChildren(clonedTable)

  dataTable = new FlowbiteDataTable(clonedTable, {
    destroyable: true,
    searchable: props.searchable,
    sortable: props.sortable,
    paging: true,
    fixedHeight: false,
    perPage: props.initialPerPage,
    perPageSelect: props.perPageOptions,
    firstLast: false,
    nextPrev: true,
    type: 'html',
    columns: dataTableColumns.value,
    template: (options) => `
      <div class="${options.classes.container}"></div>
      <div class="${options.classes.bottom} scholarsync-table-footer">
        <div class="${options.classes.info}"></div>
        <div class="scholarsync-table-controls">
          <div class="${options.classes.dropdown}">
            <label class="scholarsync-page-size">
              <span class="sr-only">Rows per page</span>
              <select class="${options.classes.selector}" aria-label="Rows per page"></select>
            </label>
          </div>
          <nav class="${options.classes.pagination}" aria-label="Table pagination"></nav>
        </div>
      </div>
    `,
    pagerRender: renderPager,
    labels: {
      placeholder: props.searchPlaceholder,
      searchTitle: 'Search within table',
      perPage: 'entries per page',
      noRows: props.emptyText || 'No records found.',
      noResults: 'No records match your search.',
      info: 'Showing {start} to {end} of {rows} entries',
    },
  })
}

function createPagerItem(page, label, state = {}) {
  const itemClasses = [
    paginationClasses.paginationListItem,
    state.active ? paginationClasses.active : '',
    state.disabled ? paginationClasses.disabled : '',
  ].filter(Boolean).join(' ')

  return {
    nodeName: 'LI',
    attributes: {
      class: itemClasses,
    },
    childNodes: [
      {
        nodeName: 'BUTTON',
        attributes: {
          'data-page': String(page),
          class: paginationClasses.paginationListItemLink,
          'aria-label': label,
          ...(state.disabled ? { disabled: 'true' } : {}),
        },
        childNodes: state.icon ? [createChevronIcon(state.icon)] : [
          {
            nodeName: '#text',
            data: label,
          },
        ],
      },
    ],
  }
}

function createChevronIcon(direction) {
  return {
    nodeName: 'svg',
    attributes: {
      class: 'h-4 w-4',
      'aria-hidden': 'true',
      xmlns: 'http://www.w3.org/2000/svg',
      width: '24',
      height: '24',
      fill: 'none',
      viewBox: '0 0 24 24',
    },
    childNodes: [
      {
        nodeName: 'path',
        attributes: {
          stroke: 'currentColor',
          'stroke-linecap': 'round',
          'stroke-linejoin': 'round',
          'stroke-width': '2',
          d: direction === 'previous' ? 'm15 19-7-7 7-7' : 'm9 5 7 7-7 7',
        },
      },
    ],
  }
}

function renderPager([onFirstPage, onLastPage, currentPage, totalPages]) {
  const pages = Math.max(totalPages, 1)
  const previousPage = onFirstPage ? 1 : currentPage - 1
  const nextPage = onLastPage ? pages : currentPage + 1

  return {
    nodeName: 'UL',
    attributes: {
      class: paginationClasses.paginationList,
    },
    childNodes: [
      createPagerItem(previousPage, 'Previous page', { disabled: onFirstPage, icon: 'previous' }),
      ...Array.from({ length: pages }, (_, index) => {
        const page = index + 1
        return createPagerItem(page, String(page), { active: page === currentPage })
      }),
      createPagerItem(nextPage, 'Next page', { disabled: onLastPage, icon: 'next' }),
    ],
  }
}

function findRowByKey(key) {
  return props.rows.find((row, index) => rowKeyAsString(row, index) === key)
}

function handleDelegatedClick(event) {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.altKey || event.ctrlKey || event.shiftKey) {
    return
  }

  const target = event.target

  if (!(target instanceof Element)) {
    return
  }

  const actionElement = target.closest('[data-table-action]')

  if (actionElement) {
    const rowElement = actionElement.closest('[data-table-row-key]')
    const rowKey = actionElement.dataset.tableRowKey || rowElement?.dataset.tableRowKey || ''
    const row = findRowByKey(rowKey)

    event.preventDefault()
    event.stopPropagation()

    if (row) {
      emit('row-action', {
        action: actionElement.dataset.tableAction,
        row,
        rowKey,
        event,
      })
    }

    return
  }

  const routeElement = target.closest('[data-table-route]')

  if (routeElement?.dataset.tableRoute) {
    event.preventDefault()
    event.stopPropagation()
    router.push(routeElement.dataset.tableRoute)
    return
  }

  const anchor = target.closest('a[href]')
  const href = anchor?.getAttribute('href')

  if (href?.startsWith('/') && !href.startsWith('//')) {
    event.preventDefault()
    event.stopPropagation()
    router.push(href)
  }
}
</script>

<template>
  <div class="scholarsync-flowbite-datatable" @click.capture="handleDelegatedClick">
    <div ref="datatableHost"></div>

    <table :id="tableId" ref="sourceTable" class="hidden" aria-hidden="true">
      <thead>
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            scope="col"
            :data-type="column.type"
            :data-format="column.format"
            :data-sortable="isColumnSortable(column)"
            :data-searchable="isColumnSearchable(column)"
          >
            <span class="flex items-center">
              {{ column.label }}
              <svg
                v-if="isColumnSortable(column)"
                class="ms-1 h-4 w-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m8 15 4 4 4-4m0-6-4-4-4 4"
                />
              </svg>
            </span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, rowIndex) in rows"
          :key="rowKeyAsString(row, rowIndex)"
          :data-table-row-key="rowKeyAsString(row, rowIndex)"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            :class="column === columns[0] ? 'font-medium text-heading whitespace-nowrap' : undefined"
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
    </table>

    <div
      v-if="hasFooterSummary"
      class="mt-4 rounded-md border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600 shadow-sm"
    >
      <slot
        name="footer-summary"
        :rows="rows"
        :paginated-rows="rows"
      ></slot>
    </div>
  </div>
</template>
