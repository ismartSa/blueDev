<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    title: String,
    user: Object,
    roles: Array,
    breadcrumbs: Array,
});

const showEditForm = ref(false);
const previewImage = ref(null);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.roles[0]?.name || '',
    password: '',
    password_confirmation: '',
    profile_picture: null,
});

const updateUser = () => {
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('email', form.email);
    formData.append('role', form.role);
    if (form.password) {
        formData.append('password', form.password);
        formData.append('password_confirmation', form.password_confirmation);
    }
    if (form.profile_picture) {
        formData.append('profile_picture', form.profile_picture);
    }
    formData.append('_method', 'PUT');

    form.post(route('user.update', props.user.id), {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            showEditForm.value = false;
            previewImage.value = null;
            form.reset('password', 'password_confirmation');
        },
    });
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_picture = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const toggleEditForm = () => {
    showEditForm.value = !showEditForm.value;
    if (!showEditForm.value) {
        form.reset();
        form.name = props.user.name;
        form.email = props.user.email;
        form.role = props.user.roles[0]?.name || '';
        previewImage.value = null;
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Breadcrumb Navigation -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link 
                            :href="route('dashboard')" 
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </Link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <Link 
                                :href="route('user.index')" 
                                class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2 transition-colors duration-200"
                            >
                                Users
                            </Link>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ user.name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Header with Back Button -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">User Profile</h1>
                        <p class="text-gray-600 mt-2">Manage user information and settings</p>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Dashboard
                        </Link>
                        
                        <button
                            @click="toggleEditForm"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ showEditForm ? 'Cancel Edit' : 'Edit User' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <div v-if="$page.props.flash.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- User Profile Card -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header with Profile Picture -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            <img 
                                :src="user.profile_picture ? `/storage/${user.profile_picture}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&color=7F9CF5&background=EBF4FF'"
                                :alt="user.name"
                                class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                            >
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-white">
                            <h1 class="text-3xl font-bold mb-2">{{ user.name }}</h1>
                            <p class="text-indigo-100 text-lg">{{ user.email }}</p>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    {{ user.roles[0]?.name || 'No Role' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="p-6">
                    <div v-if="!showEditForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <p class="text-lg font-semibold text-gray-900">{{ user.name }}</p>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <p class="text-lg font-semibold text-gray-900">{{ user.email }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <p class="text-lg font-semibold text-gray-900">{{ user.roles[0]?.name || 'No Role Assigned' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Member Since</label>
                                <p class="text-lg font-semibold text-gray-900">{{ user.created_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form v-else @submit.prevent="updateUser" class="space-y-6" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Profile Picture Upload -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                                <div class="flex items-center space-x-4">
                                    <img 
                                        :src="previewImage || (user.profile_picture ? `/storage/${user.profile_picture}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&color=7F9CF5&background=EBF4FF')"
                                        :alt="user.name"
                                        class="w-16 h-16 rounded-full object-cover border-2 border-gray-300"
                                    >
                                    <div class="flex-1">
                                        <input 
                                            ref="profilePictureInput"
                                            @change="handleFileUpload"
                                            type="file" 
                                            accept="image/*"
                                            class="hidden"
                                        >
                                        <button 
                                            type="button"
                                            @click="$refs.profilePictureInput.click()"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200"
                                        >
                                            Choose File
                                        </button>
                                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                                    </div>
                                </div>
                                <p v-if="form.errors.profile_picture" class="mt-1 text-sm text-red-600">{{ form.errors.profile_picture }}</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.email }"
                                    required
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                            
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.role }"
                                    required
                                >
                                    <option value="">Select Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password (Optional)</label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.password }"
                                    placeholder="Leave blank to keep current password"
                                />
                                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.password_confirmation }"
                                    placeholder="Confirm new password"
                                />
                                <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button
                                type="button"
                                @click="toggleEditForm"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                                <span v-else>Update User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>