<template>
  <div class="optimized-table-container">
    <!-- Header Section -->
    <div class="table-header" v-if="showHeader">
      <div class="header-left">
        <slot name="header-left">
          <h3 v-if="title" class="table-title">{{ title }}</h3>
        </slot>
      </div>
      
      <div class="header-right">
        <slot name="header-right">
          <!-- Search -->
          <div v-if="searchable" class="search-container">
            <input
              v-model="searchTerm"
              type="text"
              :placeholder="searchPlaceholder"
              class="search-input"
              @input="handleSearch"
            />
            <Icon name="search" class="search-icon" />
          </div>
          
          <!-- Filters -->
          <div v-if="filterable" class="filters-container">
            <select
              v-for="filter in availableFilters"
              :key="filter.key"
              v-model="filters[filter.key]"
              class="filter-select"
              @change="handleFilterChange"
            >
              <option value="">{{ filter.placeholder }}</option>
              <option
                v-for="option in filter.options"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>
          
          <!-- Actions -->
          <div class="actions-container">
            <button
              v-if="refreshable"
              @click="refresh"
              class="action-btn refresh-btn"
              :disabled="loading"
            >
              <Icon :name="loading ? 'spinner' : 'refresh'" :class="{ 'animate-spin': loading }" />
            </button>
            
            <button
              v-if="exportable"
              @click="handleExport"
              class="action-btn export-btn"
              :disabled="loading"
            >
              <Icon name="download" />
            </button>
            
            <slot name="actions" />
          </div>
        </slot>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="hasSelection && bulkActions.length > 0" class="bulk-actions">
      <div class="bulk-info">
        <span>{{ selectedCount }} {{ selectedCount === 1 ? 'item' : 'items' }} selected</span>
      </div>
      
      <div class="bulk-buttons">
        <button
          v-for="action in bulkActions"
          :key="action.key"
          @click="handleBulkAction(action)"
          :class="['bulk-btn', action.class]"
          :disabled="loading"
        >
          <Icon v-if="action.icon" :name="action.icon" />
          {{ action.label }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th v-if="selectable" class="select-column">
              <input
                type="checkbox"
                :checked="isAllSelected"
                :indeterminate="isIndeterminate"
                @change="selectAll"
                class="select-checkbox"
              />
            </th>
            
            <th
              v-for="column in visibleColumns"
              :key="column.key"
              :class="[
                'table-header-cell',
                column.class,
                {
                  'sortable': column.sortable,
                  'sorted': isSorted(column.key),
                  'sorted-asc': isSorted(column.key) && sorting.sortDirection === 'asc',
                  'sorted-desc': isSorted(column.key) && sorting.sortDirection === 'desc'
                }
              ]"
              @click="column.sortable ? sortBy(column.key) : null"
            >
              <div class="header-content">
                <span>{{ column.label }}</span>
                <Icon
                  v-if="column.sortable"
                  :name="getSortIcon(column.key)"
                  class="sort-icon"
                />
              </div>
            </th>
            
            <th v-if="hasActions" class="actions-column">
              Actions
            </th>
          </tr>
        </thead>
        
        <tbody>
          <!-- Loading State -->
          <tr v-if="loading" class="loading-row">
            <td :colspan="totalColumns" class="loading-cell">
              <div class="loading-content">
                <Icon name="spinner" class="animate-spin" />
                <span>Loading...</span>
              </div>
            </td>
          </tr>
          
          <!-- Empty State -->
          <tr v-else-if="!hasItems" class="empty-row">
            <td :colspan="totalColumns" class="empty-cell">
              <slot name="empty">
                <div class="empty-content">
                  <Icon name="inbox" class="empty-icon" />
                  <p>{{ emptyMessage }}</p>
                </div>
              </slot>
            </td>
          </tr>
          
          <!-- Data Rows -->
          <tr
            v-else
            v-for="(item, index) in items"
            :key="getItemKey(item, index)"
            :class="[
              'data-row',
              {
                'selected': isSelected(item),
                'hover': hoverable
              }
            ]"
            @click="handleRowClick(item, index)"
          >
            <td v-if="selectable" class="select-cell">
              <input
                type="checkbox"
                :checked="isSelected(item)"
                @change="selectItem(item)"
                @click.stop
                class="select-checkbox"
              />
            </td>
            
            <td
              v-for="column in visibleColumns"
              :key="column.key"
              :class="['data-cell', column.class]"
            >
              <slot
                :name="`cell-${column.key}`"
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
                  v-bind="column.props"
                />
                <span v-else>{{ formatColumnValue(item, column) }}</span>
              </slot>
            </td>
            
            <td v-if="hasActions" class="actions-cell">
              <slot name="actions" :item="item" :index="index">
                <div class="row-actions">
                  <button
                    v-for="action in rowActions"
                    :key="action.key"
                    @click.stop="handleRowAction(action, item, index)"
                    :class="['action-btn', action.class]"
                    :disabled="loading || (action.disabled && action.disabled(item))"
                  >
                    <Icon v-if="action.icon" :name="action.icon" />
                    <span v-if="action.label">{{ action.label }}</span>
                  </button>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && hasItems" class="pagination-container">
      <div class="pagination-info">
        <span>
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </span>
        
        <select
          v-if="perPageOptions.length > 1"
          v-model="pagination.perPage"
          @change="changePerPage($event.target.value)"
          class="per-page-select"
        >
          <option
            v-for="option in perPageOptions"
            :key="option"
            :value="option"
          >
            {{ option }} per page
          </option>
        </select>
      </div>
      
      <div class="pagination-controls">
        <button
          @click="prevPage"
          :disabled="!hasPrevPage || loading"
          class="pagination-btn"
        >
          <Icon name="chevron-left" />
          Previous
        </button>
        
        <div class="page-numbers">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'page-btn',
              {
                'active': page === pagination.currentPage,
                'disabled': loading
              }
            ]"
            :disabled="loading"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="nextPage"
          :disabled="!hasNextPage || loading"
          class="pagination-btn"
        >
          Next
          <Icon name="chevron-right" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useTable } from '@/Composables/useTable'
import { useNotifications } from '@/Composables/useNotifications'
import Icon from '@/Components/Base/Icon.vue'

// Props
const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  searchable: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  filterable: {
    type: Boolean,
    default: false
  },
  filters: {
    type: Array,
    default: () => []
  },
  selectable: {
    type: Boolean,
    default: false
  },
  paginated: {
    type: Boolean,
    default: true
  },
  perPageOptions: {
    type: Array,
    default: () => [10, 25, 50, 100]
  },
  refreshable: {
    type: Boolean,
    default: true
  },
  exportable: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: true
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  },
  rowActions: {
    type: Array,
    default: () => []
  },
  bulkActions: {
    type: Array,
    default: () => []
  },
  tableOptions: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits([
  'row-click',
  'row-action',
  'bulk-action',
  'export',
  'refresh'
])

// Composables
const table = useTable(props.resource, props.tableOptions)
const notifications = useNotifications()

// Local state
const searchTerm = ref('')
const availableFilters = ref(props.filters)

// Computed properties
const visibleColumns = computed(() => {
  return props.columns.filter(column => column.visible !== false)
})

const hasActions = computed(() => {
  return props.rowActions.length > 0 || !!$slots.actions
})

const totalColumns = computed(() => {
  let count = visibleColumns.value.length
  if (props.selectable) count++
  if (hasActions.value) count++
  return count
})

const visiblePages = computed(() => {
  const current = table.pagination.value.currentPage
  const total = table.totalPages.value
  const delta = 2
  
  const range = []
  const rangeWithDots = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (current + delta < total - 1) {
    rangeWithDots.push('...', total)
  } else if (total > 1) {
    rangeWithDots.push(total)
  }
  
  return rangeWithDots.filter(page => page !== '...' || rangeWithDots.indexOf(page) === rangeWithDots.lastIndexOf(page))
})

// Destructure table composable
const {
  items,
  loading,
  error,
  pagination,
  sorting,
  filters,
  hasItems,
  hasSelection,
  selectedCount,
  isAllSelected,
  isIndeterminate,
  hasNextPage,
  hasPrevPage,
  loadData,
  refresh,
  goToPage,
  nextPage,
  prevPage,
  changePerPage,
  sortBy,
  getSortIcon,
  isSorted,
  setSearch,
  setFilter,
  selectItem,
  selectAll,
  isSelected,
  getSelectedItems,
  bulkDelete,
  bulkUpdate,
  exportData
} = table

// Methods
const handleSearch = () => {
  setSearch(searchTerm.value)
}

const handleFilterChange = () => {
  Object.keys(filters.value).forEach(key => {
    if (availableFilters.value.find(f => f.key === key)) {
      setFilter(key, filters.value[key])
    }
  })
}

const handleRowClick = (item, index) => {
  emit('row-click', { item, index })
}

const handleRowAction = (action, item, index) => {
  emit('row-action', { action, item, index })
}

const handleBulkAction = async (action) => {
  try {
    const selectedItems = getSelectedItems()
    
    if (action.confirm) {
      const confirmed = await new Promise((resolve) => {
        notifications.confirm(
          action.confirmMessage || `Are you sure you want to ${action.label.toLowerCase()} ${selectedItems.length} items?`,
          () => resolve(true),
          () => resolve(false)
        )
      })
      
      if (!confirmed) return
    }
    
    emit('bulk-action', { action, items: selectedItems })
    
    if (action.key === 'delete') {
      await bulkDelete()
      notifications.success(`${selectedItems.length} items deleted successfully`)
    } else if (action.handler) {
      await action.handler(selectedItems)
    }
  } catch (error) {
    notifications.error(error.message || 'Bulk action failed')
  }
}

const handleExport = async () => {
  try {
    await exportData('csv')
    emit('export')
    notifications.success('Export completed successfully')
  } catch (error) {
    notifications.error('Export failed')
  }
}

const getItemKey = (item, index) => {
  return item.id || item.uuid || index
}

const getColumnValue = (item, column) => {
  if (column.key.includes('.')) {
    return column.key.split('.').reduce((obj, key) => obj?.[key], item)
  }
  return item[column.key]
}

const formatColumnValue = (item, column) => {
  const value = getColumnValue(item, column)
  
  if (column.formatter) {
    return column.formatter(value, item)
  }
  
  if (column.type === 'date' && value) {
    return new Date(value).toLocaleDateString()
  }
  
  if (column.type === 'datetime' && value) {
    return new Date(value).toLocaleString()
  }
  
  if (column.type === 'currency' && value !== null && value !== undefined) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(value)
  }
  
  if (column.type === 'number' && value !== null && value !== undefined) {
    return new Intl.NumberFormat().format(value)
  }
  
  if (column.type === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  
  return value || ''
}

// Watch for prop changes
watch(() => props.filters, (newFilters) => {
  availableFilters.value = newFilters
}, { deep: true })

// Handle errors
watch(error, (newError) => {
  if (newError) {
    notifications.error(newError)
  }
})

// Refresh on mount
onMounted(() => {
  emit('refresh')
})
</script>

<style scoped>
.optimized-table-container {
  @apply bg-white rounded-lg shadow-sm border border-gray-200;
}

.table-header {
  @apply flex items-center justify-between p-4 border-b border-gray-200;
}

.header-left {
  @apply flex items-center space-x-4;
}

.header-right {
  @apply flex items-center space-x-3;
}

.table-title {
  @apply text-lg font-semibold text-gray-900;
}

.search-container {
  @apply relative;
}

.search-input {
  @apply pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.search-icon {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4;
}

.filters-container {
  @apply flex items-center space-x-2;
}

.filter-select {
  @apply px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.actions-container {
  @apply flex items-center space-x-2;
}

.action-btn {
  @apply px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors;
}

.refresh-btn {
  @apply text-gray-600;
}

.export-btn {
  @apply text-blue-600;
}

.bulk-actions {
  @apply flex items-center justify-between p-4 bg-blue-50 border-b border-blue-200;
}

.bulk-info {
  @apply text-sm text-blue-700 font-medium;
}

.bulk-buttons {
  @apply flex items-center space-x-2;
}

.bulk-btn {
  @apply px-3 py-1 text-sm rounded-md font-medium transition-colors;
}

.table-wrapper {
  @apply overflow-x-auto;
}

.data-table {
  @apply w-full;
}

.data-table th {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200;
}

.data-table td {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b border-gray-200;
}

.table-header-cell.sortable {
  @apply cursor-pointer hover:bg-gray-50 transition-colors;
}

.header-content {
  @apply flex items-center space-x-1;
}

.sort-icon {
  @apply w-4 h-4 text-gray-400;
}

.sorted .sort-icon {
  @apply text-blue-500;
}

.select-column,
.actions-column {
  @apply w-12;
}

.select-checkbox {
  @apply rounded border-gray-300 text-blue-600 focus:ring-blue-500;
}

.data-row.hover:hover {
  @apply bg-gray-50;
}

.data-row.selected {
  @apply bg-blue-50;
}

.loading-row,
.empty-row {
  @apply bg-gray-50;
}

.loading-cell,
.empty-cell {
  @apply text-center py-12;
}

.loading-content,
.empty-content {
  @apply flex flex-col items-center space-y-2;
}

.empty-icon {
  @apply w-12 h-12 text-gray-400;
}

.row-actions {
  @apply flex items-center space-x-1;
}

.pagination-container {
  @apply flex items-center justify-between p-4 border-t border-gray-200;
}

.pagination-info {
  @apply flex items-center space-x-4 text-sm text-gray-700;
}

.per-page-select {
  @apply px-2 py-1 border border-gray-300 rounded text-sm;
}

.pagination-controls {
  @apply flex items-center space-x-1;
}

.pagination-btn {
  @apply px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.page-numbers {
  @apply flex items-center space-x-1;
}

.page-btn {
  @apply px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.page-btn.active {
  @apply bg-blue-500 text-white border-blue-500;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>