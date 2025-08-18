<template>
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb title="Categories" :breadcrumbs="[{ name: 'Dashboard', href: route('dashboard') }]" />
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your course categories</p>
              </div>
              <div class="mt-4 sm:mt-0">
                <PrimaryButton @click="showCreateModal = true" class="w-full sm:w-auto">
                  Add Category
                </PrimaryButton>
              </div>
            </div>

            <div class="mb-8">
              <div class="max-w-md">
                <TextInput 
                  v-model="search" 
                  placeholder="Search categories..." 
                  @input="searchCategories" 
                  class="w-full" 
                />
              </div>
            </div>

            <div v-if="categories.data && categories.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="category in categories.data" :key="category.id" class="bg-white dark:bg-slate-700 rounded-lg shadow-sm border border-gray-200 dark:border-slate-600 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                  <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ category.name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                      {{ category.courses_count }} courses
                    </span>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 text-sm mb-6 min-h-[3rem]">{{ category.description || 'No description available.' }}</p>
                  <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-slate-600">
                    <SecondaryButton @click="editCategory(category)" class="text-sm px-4 py-2">
                      Edit
                    </SecondaryButton>
                    <DangerButton @click="deleteCategory(category)" class="text-sm px-4 py-2">
                      Delete
                    </DangerButton>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories found</h3>
              <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ search ? 'Try adjusting your search terms.' : 'Get started by creating your first category.' }}
              </p>
              <PrimaryButton v-if="!search" @click="showCreateModal = true">
                Create Category
              </PrimaryButton>
            </div>

            <div v-if="categories.data && categories.data.length > 0" class="mt-8">
              <Pagination :links="categories?.links || { data: [], links: [] }" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showCreateModal || showEditModal" @close="closeModal">
      <div class="p-6 bg-white rounded-lg shadow-xl max-w-md mx-auto">
        <h2 class="text-xl font-semibold mb-6 text-gray-700">{{ editingCategory ? 'Edit' : 'Create New' }} Category</h2>
        <form @submit.prevent="submitForm">
          <div class="mb-5">
            <InputLabel for="name" value="Category Name" class="mb-1" />
            <TextInput id="name" v-model="form.name" required class="w-full" placeholder="e.g., Programming" />
          </div>
          <div class="mb-6">
            <InputLabel for="description" value="Description" class="mb-1" />
            <textarea id="description" v-model="form.description" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-24" placeholder="A brief description of the category"></textarea>
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <PrimaryButton type="submit" :disabled="form.processing">{{ editingCategory ? 'Update Category' : 'Create Category' }}</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3'; // Import useForm
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({ // Simplified props definition
  categories: Object,
  filters: Object
});

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingCategory = ref(null);

// Use Inertia's useForm for better form handling
const form = useForm({
  name: '',
  description: ''
});

const searchCategories = () => {
  router.get(route('admin.category.index'), { search: search.value }, {
    preserveState: true,
    replace: true // Avoids polluting browser history with search queries
  });
};

const editCategory = (category) => {
  editingCategory.value = category;
  form.name = category.name;
  form.description = category.description;
  showEditModal.value = true;
};

const deleteCategory = (category) => {
  if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
    router.delete(route('admin.category.destroy', category.id), {
      preserveScroll: true, // Keep scroll position after delete
      onSuccess: () => {
        // Optionally, add a success notification here
      }
    });
  }
};

const submitForm = () => {
  if (editingCategory.value) {
    form.put(route('admin.category.update', editingCategory.value.id), {
      preserveScroll: true,
      onSuccess: () => closeModal()
    });
  } else {
    form.post(route('admin.category.store'), {
      preserveScroll: true,
      onSuccess: () => closeModal()
    });
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingCategory.value = null;
  form.reset(); // Reset form fields and errors
};
</script>
