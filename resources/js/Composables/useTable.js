import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useApi } from './useApi'

/**
 * Composable for table data management with sorting, filtering, and pagination
 * Implements DRY principle for common table patterns
 */
export function useTable(resource, options = {}) {
  // Default options
  const defaultOptions = {
    perPage: 15,
    sortBy: 'id',
    sortDirection: 'desc',
    searchDebounce: 300,
    preserveState: true,
    preserveScroll: true,
    autoLoad: true,
    ...options
  }

  // API composable
  const api = useApi(`/api/${resource}`)

  // Table state
  const state = reactive({
    items: [],
    selectedItems: new Set(),
    allSelected: false,
    loading: false,
    error: null
  })

  // Pagination state
  const pagination = reactive({
    currentPage: 1,
    perPage: defaultOptions.perPage,
    total: 0,
    lastPage: 1,
    from: 0,
    to: 0
  })

  // Sorting state
  const sorting = reactive({
    sortBy: defaultOptions.sortBy,
    sortDirection: defaultOptions.sortDirection
  })

  // Filtering state
  const filters = reactive({
    search: '',
    status: '',
    dateFrom: '',
    dateTo: '',
    ...options.defaultFilters
  })

  // Search debounce timer
  let searchTimer = null

  // Computed properties
  const hasItems = computed(() => state.items.length > 0)
  const hasSelection = computed(() => state.selectedItems.size > 0)
  const selectedCount = computed(() => state.selectedItems.size)
  const isAllSelected = computed(() => {
    return hasItems.value && state.selectedItems.size === state.items.length
  })
  const isIndeterminate = computed(() => {
    return hasSelection.value && !isAllSelected.value
  })

  const totalPages = computed(() => pagination.lastPage)
  const hasNextPage = computed(() => pagination.currentPage < pagination.lastPage)
  const hasPrevPage = computed(() => pagination.currentPage > 1)

  const queryParams = computed(() => {
    const params = {
      page: pagination.currentPage,
      per_page: pagination.perPage,
      sort_by: sorting.sortBy,
      sort_direction: sorting.sortDirection
    }

    // Add non-empty filters
    Object.keys(filters).forEach(key => {
      if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
        params[key] = filters[key]
      }
    })

    return params
  })

  /**
   * Load table data
   */
  const loadData = async (params = {}) => {
    state.loading = true
    state.error = null

    try {
      const response = await api.get('', { ...queryParams.value, ...params })
      
      if (response) {
        state.items = response.data || []
        
        // Update pagination from meta
        if (response.meta) {
          Object.assign(pagination, {
            currentPage: response.meta.current_page || 1,
            perPage: response.meta.per_page || defaultOptions.perPage,
            total: response.meta.total || 0,
            lastPage: response.meta.last_page || 1,
            from: response.meta.from || 0,
            to: response.meta.to || 0
          })
        }
        
        // Clear selection when data changes
        clearSelection()
      }
    } catch (error) {
      state.error = error.message || 'Failed to load data'
      console.error('Table load error:', error)
    } finally {
      state.loading = false
    }
  }

  /**
   * Refresh current data
   */
  const refresh = () => {
    return loadData()
  }

  /**
   * Go to specific page
   */
  const goToPage = (page) => {
    if (page >= 1 && page <= pagination.lastPage) {
      pagination.currentPage = page
      loadData()
    }
  }

  /**
   * Go to next page
   */
  const nextPage = () => {
    if (hasNextPage.value) {
      goToPage(pagination.currentPage + 1)
    }
  }

  /**
   * Go to previous page
   */
  const prevPage = () => {
    if (hasPrevPage.value) {
      goToPage(pagination.currentPage - 1)
    }
  }

  /**
   * Change items per page
   */
  const changePerPage = (perPage) => {
    pagination.perPage = perPage
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Sort by column
   */
  const sortBy = (column) => {
    if (sorting.sortBy === column) {
      // Toggle direction if same column
      sorting.sortDirection = sorting.sortDirection === 'asc' ? 'desc' : 'asc'
    } else {
      // New column, default to ascending
      sorting.sortBy = column
      sorting.sortDirection = 'asc'
    }
    
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Get sort icon for column
   */
  const getSortIcon = (column) => {
    if (sorting.sortBy !== column) return 'sort'
    return sorting.sortDirection === 'asc' ? 'sort-up' : 'sort-down'
  }

  /**
   * Check if column is sorted
   */
  const isSorted = (column) => {
    return sorting.sortBy === column
  }

  /**
   * Set search term with debounce
   */
  const setSearch = (term) => {
    filters.search = term
    
    if (searchTimer) {
      clearTimeout(searchTimer)
    }
    
    searchTimer = setTimeout(() => {
      pagination.currentPage = 1
      loadData()
    }, defaultOptions.searchDebounce)
  }

  /**
   * Set filter value
   */
  const setFilter = (key, value) => {
    filters[key] = value
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Set multiple filters
   */
  const setFilters = (newFilters) => {
    Object.assign(filters, newFilters)
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Clear all filters
   */
  const clearFilters = () => {
    Object.keys(filters).forEach(key => {
      if (options.defaultFilters && options.defaultFilters[key] !== undefined) {
        filters[key] = options.defaultFilters[key]
      } else {
        filters[key] = ''
      }
    })
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Clear specific filter
   */
  const clearFilter = (key) => {
    if (options.defaultFilters && options.defaultFilters[key] !== undefined) {
      filters[key] = options.defaultFilters[key]
    } else {
      filters[key] = ''
    }
    pagination.currentPage = 1
    loadData()
  }

  /**
   * Select item
   */
  const selectItem = (item) => {
    const id = item.id || item
    if (state.selectedItems.has(id)) {
      state.selectedItems.delete(id)
    } else {
      state.selectedItems.add(id)
    }
    updateAllSelectedState()
  }

  /**
   * Select all items
   */
  const selectAll = () => {
    if (isAllSelected.value) {
      clearSelection()
    } else {
      state.items.forEach(item => {
        state.selectedItems.add(item.id)
      })
      updateAllSelectedState()
    }
  }

  /**
   * Clear selection
   */
  const clearSelection = () => {
    state.selectedItems.clear()
    updateAllSelectedState()
  }

  /**
   * Update all selected state
   */
  const updateAllSelectedState = () => {
    state.allSelected = isAllSelected.value
  }

  /**
   * Check if item is selected
   */
  const isSelected = (item) => {
    const id = item.id || item
    return state.selectedItems.has(id)
  }

  /**
   * Get selected items data
   */
  const getSelectedItems = () => {
    return state.items.filter(item => state.selectedItems.has(item.id))
  }

  /**
   * Get selected IDs
   */
  const getSelectedIds = () => {
    return Array.from(state.selectedItems)
  }

  /**
   * Bulk delete selected items
   */
  const bulkDelete = async () => {
    if (!hasSelection.value) return

    const ids = getSelectedIds()
    
    try {
      await api.post('/bulk-delete', { ids })
      await refresh()
      clearSelection()
    } catch (error) {
      console.error('Bulk delete error:', error)
      throw error
    }
  }

  /**
   * Bulk update selected items
   */
  const bulkUpdate = async (data) => {
    if (!hasSelection.value) return

    const ids = getSelectedIds()
    
    try {
      await api.post('/bulk-update', { ids, ...data })
      await refresh()
      clearSelection()
    } catch (error) {
      console.error('Bulk update error:', error)
      throw error
    }
  }

  /**
   * Export data
   */
  const exportData = async (format = 'csv', params = {}) => {
    try {
      const response = await api.get('/export', {
        format,
        ...queryParams.value,
        ...params
      })
      
      // Handle file download
      if (response.data.download_url) {
        window.open(response.data.download_url, '_blank')
      }
      
      return response
    } catch (error) {
      console.error('Export error:', error)
      throw error
    }
  }

  /**
   * Reset table state
   */
  const reset = () => {
    // Reset pagination
    pagination.currentPage = 1
    pagination.perPage = defaultOptions.perPage
    
    // Reset sorting
    sorting.sortBy = defaultOptions.sortBy
    sorting.sortDirection = defaultOptions.sortDirection
    
    // Reset filters
    clearFilters()
    
    // Clear selection
    clearSelection()
    
    // Reload data
    loadData()
  }

  /**
   * Update item in table
   */
  const updateItem = (id, updatedData) => {
    const index = state.items.findIndex(item => item.id === id)
    if (index !== -1) {
      state.items[index] = { ...state.items[index], ...updatedData }
    }
  }

  /**
   * Remove item from table
   */
  const removeItem = (id) => {
    state.items = state.items.filter(item => item.id !== id)
    state.selectedItems.delete(id)
    updateAllSelectedState()
    
    // Update pagination total
    pagination.total = Math.max(0, pagination.total - 1)
  }

  /**
   * Add item to table
   */
  const addItem = (item) => {
    state.items.unshift(item)
    pagination.total += 1
  }

  // Auto-load data on mount
  if (defaultOptions.autoLoad) {
    loadData()
  }

  // Watch for URL changes if preserveState is enabled
  if (defaultOptions.preserveState) {
    watch(
      () => router.page.url,
      () => {
        // Update state from URL params if needed
        const urlParams = new URLSearchParams(window.location.search)
        
        if (urlParams.has('page')) {
          pagination.currentPage = parseInt(urlParams.get('page')) || 1
        }
        
        if (urlParams.has('search')) {
          filters.search = urlParams.get('search') || ''
        }
      },
      { immediate: true }
    )
  }

  return {
    // State
    items: computed(() => state.items),
    loading: computed(() => state.loading),
    error: computed(() => state.error),
    
    // Pagination
    pagination: computed(() => pagination),
    totalPages,
    hasNextPage,
    hasPrevPage,
    
    // Sorting
    sorting: computed(() => sorting),
    
    // Filtering
    filters: computed(() => filters),
    
    // Selection
    selectedItems: computed(() => state.selectedItems),
    hasSelection,
    selectedCount,
    isAllSelected,
    isIndeterminate,
    
    // Computed
    hasItems,
    queryParams,
    
    // Methods - Data
    loadData,
    refresh,
    reset,
    updateItem,
    removeItem,
    addItem,
    
    // Methods - Pagination
    goToPage,
    nextPage,
    prevPage,
    changePerPage,
    
    // Methods - Sorting
    sortBy,
    getSortIcon,
    isSorted,
    
    // Methods - Filtering
    setSearch,
    setFilter,
    setFilters,
    clearFilters,
    clearFilter,
    
    // Methods - Selection
    selectItem,
    selectAll,
    clearSelection,
    isSelected,
    getSelectedItems,
    getSelectedIds,
    
    // Methods - Bulk operations
    bulkDelete,
    bulkUpdate,
    exportData
  }
}

/**
 * Composable for simple data lists without pagination
 */
export function useList(resource, options = {}) {
  const api = useApi(`/api/${resource}`)
  
  const state = reactive({
    items: [],
    loading: false,
    error: null
  })
  
  const filters = reactive({
    search: '',
    ...options.defaultFilters
  })
  
  const loadData = async (params = {}) => {
    state.loading = true
    state.error = null
    
    try {
      const response = await api.get('', { ...filters, ...params })
      if (response) {
        state.items = response.data || []
      }
    } catch (error) {
      state.error = error.message || 'Failed to load data'
    } finally {
      state.loading = false
    }
  }
  
  const setFilter = (key, value) => {
    filters[key] = value
    loadData()
  }
  
  const clearFilters = () => {
    Object.keys(filters).forEach(key => {
      filters[key] = options.defaultFilters?.[key] || ''
    })
    loadData()
  }
  
  // Auto-load data
  if (options.autoLoad !== false) {
    loadData()
  }
  
  return {
    items: computed(() => state.items),
    loading: computed(() => state.loading),
    error: computed(() => state.error),
    filters: computed(() => filters),
    
    loadData,
    setFilter,
    clearFilters,
    refresh: loadData
  }
}