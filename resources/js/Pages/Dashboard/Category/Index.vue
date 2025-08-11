<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Categories</h1>
        <PrimaryButton @click="showCreateModal = true">Add Category</PrimaryButton>
      </div>

      <div class="mb-6">
        <TextInput v-model="search" placeholder="Search categories..." @input="searchCategories" class="w-full md:w-1/3" />
      </div>

      <div v-if="categories.data && categories.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="category in categories.data" :key="category.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-gray-800">{{ category.name }}</h3>
            <p class="text-gray-600 text-sm mb-3 h-16 overflow-y-auto">{{ category.description || 'No description available.' }}</p>
            <div class="flex justify-between items-center mb-4">
              <span class="text-sm text-gray-500">Courses: {{ category.courses_count }}</span>
            </div>
            <div class="flex justify-end space-x-2 border-t pt-4 mt-4">
              <SecondaryButton @click="editCategory(category)" class="text-sm">Edit</SecondaryButton>
              <DangerButton @click="deleteCategory(category)" class="text-sm">Delete</DangerButton>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center text-gray-500 py-10">
        <p class="text-xl">No categories found.</p>
        <p v-if="search">Try adjusting your search terms.</p>
      </div>

      <Pagination :links="categories?.links || { data: [], links: [] }" class="mt-8" />
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
