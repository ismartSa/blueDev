<template>
  <div class="data-table-container">
    <!-- Header with search and actions -->
    <div class="table-header" v-if="showHeader">
      <div class="search-section">
        <input
          v-if="searchable"
          v-model="searchTerm"
          type="text"
          :placeholder="searchPlaceholder"
          class="search-input"
          @input="debouncedSearch"
        />
        <select
          v-if="filterable && filterOptions.length"
          v-model="selectedFilter"
          class="filter-select"
          @change="applyFilter"
        >
          <option value="">{{ filterPlaceholder }}</option>
          <option
            v-for="option in filterOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </div>
      
      <div class="actions-section">
        <button
          v-if="bulkActions && selectedRows.length"
          v-for="action in bulkActions"
          :key="action.name"
          @click="executeBulkAction(action)"
          :class="['btn', action.class || 'btn-secondary']"
        >
          {{ action.label }} ({{ selectedRows.length }})
        </button>
        
        <slot name="actions" :selected="selectedRows" />
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper" :class="{ 'loading': loading }">
      <table class="data-table">
        <thead>
          <tr>
            <th v-if="selectable" class="select-column">
              <input
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleSelectAll"
              />
            </th>
            <th
              v-for="column in visibleColumns"
              :key="column.key"
              :class="[
                'column-header',
                column.sortable ? 'sortable' : '',
                getSortClass(column.key)
              ]"
              @click="column.sortable && toggleSort(column.key)"
            >
              <div class="header-content">
                <span>{{ column.label }}</span>
                <span v-if="column.sortable" class="sort-icon">
                  <i :class="getSortIcon(column.key)"></i>
                </span>
              </div>
            </th>
            <th v-if="hasActions" class="actions-column">Actions</th>
          </tr>
        </thead>
        
        <tbody>
          <tr v-if="loading" class="loading-row">
            <td :colspan="totalColumns" class="loading-cell">
              <div class="loading-spinner"></div>
              <span>Loading...</span>
            </td>
          </tr>
          
          <tr v-else-if="!items.length" class="empty-row">
            <td :colspan="totalColumns" class="empty-cell">
              <div class="empty-state">
                <i class="empty-icon"></i>
                <p>{{ emptyMessage }}</p>
              </div>
            </td>
          </tr>
          
          <tr
            v-else
            v-for="(item, index) in items"
            :key="getItemKey(item, index)"
            :class="[
              'data-row',
              { 'selected': isSelected(item) },
              getRowClass(item, index)
            ]"
            @click="rowClickable && handleRowClick(item, index)"
          >
            <td v-if="selectable" class="select-cell">
              <input
                type="checkbox"
                :checked="isSelected(item)"
                @change="toggleSelect(item)"
                @click.stop
              />
            </td>
            
            <td
              v-for="column in visibleColumns"
              :key="column.key"
              :class="['data-cell', column.class]"
            >
              <slot
                :name="`column.${column.key}`"
                :item="item"
                :value="getColumnValue(item, column)"
                :column="column"
                :index="index"
              >
                <component
                  v-if="column.component"
                  :is="column.component"
                  :item="item"
                  :value="getColumnValue(item, column)"
                  v-bind="column.props || {}"
                />
                <span v-else-if="column.html" v-html="getColumnValue(item, column)"></span>
                <span v-else>{{ formatColumnValue(item, column) }}</span>
              </slot>
            </td>
            
            <td v-if="hasActions" class="actions-cell">
              <div class="action-buttons">
                <button
                  v-for="action in getItemActions(item)"
                  :key="action.name"
                  @click.stop="executeAction(action, item, index)"
                  :class="['btn', 'btn-sm', action.class || 'btn-outline']"
                  :title="action.title || action.label"
                  :disabled="action.disabled && action.disabled(item)"
                >
                  <i v-if="action.icon" :class="action.icon"></i>
                  <span v-if="!action.iconOnly">{{ action.label }}</span>
                </button>
                
                <slot name="actions" :item="item" :index="index" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && pagination" class="pagination-wrapper">
      <div class="pagination-info">
        Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} results
      </div>
      
      <nav class="pagination-nav">
        <button
          :disabled="!pagination.prev_page_url"
          @click="changePage(pagination.current_page - 1)"
          class="btn btn-outline btn-sm"
        >
          Previous
        </button>
        
        <button
          v-for="page in paginationPages"
          :key="page"
          @click="changePage(page)"
          :class="[
            'btn', 'btn-sm',
            page === pagination.current_page ? 'btn-primary' : 'btn-outline'
          ]"
        >
          {{ page }}
        </button>
        
        <button
          :disabled="!pagination.next_page_url"
          @click="changePage(pagination.current_page + 1)"
          class="btn btn-outline btn-sm"
        >
          Next
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { debounce } from 'lodash-es'

// Props
const props = defineProps({
  items: { type: Array, default: () => [] },
  columns: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  searchable: { type: Boolean, default: true },
  filterable: { type: Boolean, default: false },
  selectable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: true },
  paginated: { type: Boolean, default: false },
  rowClickable: { type: Boolean, default: false },
  showHeader: { type: Boolean, default: true },
  
  // Data
  pagination: { type: Object, default: null },
  filterOptions: { type: Array, default: () => [] },
  actions: { type: Array, default: () => [] },
  bulkActions: { type: Array, default: () => [] },
  
  // Configuration
  searchPlaceholder: { type: String, default: 'Search...' },
  filterPlaceholder: { type: String, default: 'Filter by...' },
  emptyMessage: { type: String, default: 'No data available' },
  itemKey: { type: String, default: 'id' },
  
  // Styling
  tableClass: { type: String, default: '' },
  rowClass: { type: [String, Function], default: '' }
})

// Emits
const emit = defineEmits([
  'search',
  'filter',
  'sort',
  'page-change',
  'row-click',
  'selection-change',
  'action',
  'bulk-action'
])

// Reactive data
const searchTerm = ref('')
const selectedFilter = ref('')
const selectedRows = ref([])
const sortField = ref('')
const sortDirection = ref('asc')

// Computed properties
const visibleColumns = computed(() => {
  return props.columns.filter(col => col.visible !== false)
})

const hasActions = computed(() => {
  return props.actions.length > 0 || $slots.actions
})

const totalColumns = computed(() => {
  let count = visibleColumns.value.length
  if (props.selectable) count++
  if (hasActions.value) count++
  return count
})

const allSelected = computed(() => {
  return props.items.length > 0 && selectedRows.value.length === props.items.length
})

const someSelected = computed(() => {
  return selectedRows.value.length > 0 && selectedRows.value.length < props.items.length
})

const paginationPages = computed(() => {
  if (!props.pagination) return []
  
  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const pages = []
  
  // Show first page
  if (current > 3) pages.push(1)
  if (current > 4) pages.push('...')
  
  // Show pages around current
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  // Show last page
  if (current < last - 3) pages.push('...')
  if (current < last - 2) pages.push(last)
  
  return pages
})

// Methods
const debouncedSearch = debounce(() => {
  emit('search', searchTerm.value)
}, 300)

const applyFilter = () => {
  emit('filter', selectedFilter.value)
}

const toggleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  
  emit('sort', { field: sortField.value, direction: sortDirection.value })
}

const getSortClass = (field) => {
  if (sortField.value !== field) return ''
  return `sorted-${sortDirection.value}`
}

const getSortIcon = (field) => {
  if (sortField.value !== field) return 'fas fa-sort'
  return sortDirection.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = [...props.items]
  }
  emit('selection-change', selectedRows.value)
}

const toggleSelect = (item) => {
  const index = selectedRows.value.findIndex(row => 
    getItemKey(row) === getItemKey(item)
  )
  
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(item)
  }
  
  emit('selection-change', selectedRows.value)
}

const isSelected = (item) => {
  return selectedRows.value.some(row => 
    getItemKey(row) === getItemKey(item)
  )
}

const getItemKey = (item, index = 0) => {
  return item[props.itemKey] || index
}

const getColumnValue = (item, column) => {
  if (column.value && typeof column.value === 'function') {
    return column.value(item)
  }
  
  return column.key.split('.').reduce((obj, key) => obj?.[key], item)
}

const formatColumnValue = (item, column) => {
  const value = getColumnValue(item, column)
  
  if (column.format && typeof column.format === 'function') {
    return column.format(value, item)
  }
  
  if (column.type === 'date' && value) {
    return new Date(value).toLocaleDateString()
  }
  
  if (column.type === 'currency' && value !== null && value !== undefined) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(value)
  }
  
  return value
}

const getRowClass = (item, index) => {
  if (typeof props.rowClass === 'function') {
    return props.rowClass(item, index)
  }
  return props.rowClass
}

const getItemActions = (item) => {
  return props.actions.filter(action => {
    if (action.condition && typeof action.condition === 'function') {
      return action.condition(item)
    }
    return true
  })
}

const handleRowClick = (item, index) => {
  emit('row-click', { item, index })
}

const executeAction = (action, item, index) => {
  emit('action', { action: action.name, item, index, actionData: action })
}

const executeBulkAction = (action) => {
  emit('bulk-action', { action: action.name, items: selectedRows.value, actionData: action })
}

const changePage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page) {
    emit('page-change', page)
  }
}

// Watchers
watch(() => props.items, () => {
  // Clear selection when items change
  selectedRows.value = []
}, { deep: true })

// Slots
const $slots = defineSlots()
</script>

<style scoped>
.data-table-container {
  @apply bg-white rounded-lg shadow-sm border border-gray-200;
}

.table-header {
  @apply flex justify-between items-center p-4 border-b border-gray-200;
}

.search-section {
  @apply flex gap-3;
}

.search-input {
  @apply px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.filter-select {
  @apply px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.actions-section {
  @apply flex gap-2;
}

.table-wrapper {
  @apply overflow-x-auto;
}

.table-wrapper.loading {
  @apply opacity-75;
}

.data-table {
  @apply w-full divide-y divide-gray-200;
}

.column-header {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.column-header.sortable {
  @apply cursor-pointer hover:bg-gray-50 select-none;
}

.header-content {
  @apply flex items-center justify-between;
}

.sort-icon {
  @apply ml-2 text-gray-400;
}

.data-row {
  @apply hover:bg-gray-50 transition-colors;
}

.data-row.selected {
  @apply bg-blue-50;
}

.data-row.clickable {
  @apply cursor-pointer;
}

.data-cell {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

.select-cell, .select-column {
  @apply w-12 px-6 py-4;
}

.actions-cell, .actions-column {
  @apply px-6 py-4 text-right;
}

.action-buttons {
  @apply flex gap-2 justify-end;
}

.loading-row, .empty-row {
  @apply bg-gray-50;
}

.loading-cell, .empty-cell {
  @apply px-6 py-12 text-center;
}

.loading-spinner {
  @apply inline-block w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full animate-spin mr-3;
}

.empty-state {
  @apply text-gray-500;
}

.empty-icon {
  @apply text-4xl mb-2;
}

.pagination-wrapper {
  @apply flex justify-between items-center p-4 border-t border-gray-200;
}

.pagination-info {
  @apply text-sm text-gray-700;
}

.pagination-nav {
  @apply flex gap-1;
}

.btn {
  @apply px-3 py-1 rounded border font-medium transition-colors;
}

.btn-sm {
  @apply px-2 py-1 text-xs;
}

.btn-primary {
  @apply bg-blue-600 text-white border-blue-600 hover:bg-blue-700;
}

.btn-secondary {
  @apply bg-gray-600 text-white border-gray-600 hover:bg-gray-700;
}

.btn-outline {
  @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-50;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>