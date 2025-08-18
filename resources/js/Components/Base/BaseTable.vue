<template>
  <div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
        <div class="flex items-center space-x-3">
          <!-- Search -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @input="handleSearch"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          
          <!-- Actions -->
          <slot name="actions" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th v-if="selectable" class="px-6 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </th>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="handleSort(column.key)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <svg v-if="sortField === column.key" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="sortOrder === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                  <path v-else d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                </svg>
              </div>
            </th>
            <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, index) in items" :key="getItemKey(item, index)" class="hover:bg-gray-50">
            <td v-if="selectable" class="px-6 py-4">
              <input
                type="checkbox"
                :checked="selectedItems.includes(getItemKey(item, index))"
                @change="toggleSelect(getItemKey(item, index))"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </td>
            <td v-for="column in columns" :key="column.key" class="px-6 py-4 whitespace-nowrap">
              <slot :name="`cell-${column.key}`" :item="item" :value="getNestedValue(item, column.key)">
                <span class="text-sm text-gray-900">{{ getNestedValue(item, column.key) }}</span>
              </slot>
            </td>
            <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :item="item" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex items-center space-x-2">
          <button
            v-for="link in pagination.links"
            :key="link.label"
            :disabled="!link.url"
            @click="handlePageChange(link.url)"
            class="px-3 py-2 text-sm border rounded-md"
            :class="{
              'bg-blue-500 text-white border-blue-500': link.active,
              'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active && link.url,
              'bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed': !link.url
            }"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  title: { type: String, required: true },
  items: { type: Array, required: true },
  columns: { type: Array, required: true },
  pagination: { type: Object, default: null },
  selectable: { type: Boolean, default: false },
  hasActions: { type: Boolean, default: true },
  searchPlaceholder: { type: String, default: 'Search...' },
  initialSearch: { type: String, default: '' },
  initialSort: { type: Object, default: () => ({ field: 'created_at', order: 'desc' }) }
})

// Emits
const emit = defineEmits(['search', 'sort', 'select', 'page-change'])

// Reactive data
const searchQuery = ref(props.initialSearch)
const sortField = ref(props.initialSort.field)
const sortOrder = ref(props.initialSort.order)
const selectedItems = ref([])

// Computed
const isAllSelected = computed(() => {
  return props.items.length > 0 && selectedItems.value.length === props.items.length
})

// Methods
const getItemKey = (item, index) => item.id || index

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const handleSearch = () => {
  emit('search', searchQuery.value)
  updateUrl()
}

const handleSort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = 'asc'
  }
  emit('sort', { field: sortField.value, order: sortOrder.value })
  updateUrl()
}

const toggleSelect = (key) => {
  const index = selectedItems.value.indexOf(key)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(key)
  }
  emit('select', selectedItems.value)
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = props.items.map((item, index) => getItemKey(item, index))
  }
  emit('select', selectedItems.value)
}

const handlePageChange = (url) => {
  if (url) {
    emit('page-change', url)
    router.visit(url, { preserveState: true })
  }
}

const updateUrl = () => {
  const params = new URLSearchParams(window.location.search)
  
  if (searchQuery.value) {
    params.set('search', searchQuery.value)
  } else {
    params.delete('search')
  }
  
  params.set('field', sortField.value)
  params.set('order', sortOrder.value)
  
  const newUrl = `${window.location.pathname}?${params.toString()}`
  router.visit(newUrl, { preserveState: true, replace: true })
}
</script>