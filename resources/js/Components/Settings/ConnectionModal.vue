<template>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" @click="$emit('close')">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6" @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ editing ? 'Edit Connection' : 'Add New Connection' }}
                </h3>
                <button 
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-md transition-colors duration-200"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
            
            <!-- Modal Form -->
            <form @submit.prevent="$emit('save')" class="space-y-4">
                <!-- Name and Driver Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input 
                            type="text"
                            :value="form.name"
                            @input="$emit('update:form', { ...form, name: $event.target.value })"
                            placeholder="Connection name"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver</label>
                        <select 
                            :value="form.driver"
                            @change="$emit('update:form', { ...form, driver: $event.target.value })"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        >
                            <option value="">Select driver</option>
                            <option v-for="driver in drivers" :key="driver" :value="driver">
                                {{ driver.toUpperCase() }}
                            </option>
                        </select>
                    </div>
                </div>
                
                <!-- Host and Port Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Host</label>
                        <input 
                            type="text"
                            :value="form.host"
                            @input="$emit('update:form', { ...form, host: $event.target.value })"
                            placeholder="localhost"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                        <input 
                            type="number"
                            :value="form.port"
                            @input="$emit('update:form', { ...form, port: $event.target.value })"
                            placeholder="3306"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        />
                    </div>
                </div>
                
                <!-- Database Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Database</label>
                    <input 
                        type="text"
                        :value="form.database"
                        @input="$emit('update:form', { ...form, database: $event.target.value })"
                        placeholder="Database name"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                    />
                </div>
                
                <!-- Username and Password Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                        <input 
                            type="text"
                            :value="form.username"
                            @input="$emit('update:form', { ...form, username: $event.target.value })"
                            placeholder="Username"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input 
                            type="password"
                            :value="form.password"
                            @input="$emit('update:form', { ...form, password: $event.target.value })"
                            placeholder="Password"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm transition-colors duration-200"
                        />
                    </div>
                </div>
                
                <!-- Active Checkbox -->
                <div class="flex items-center">
                    <input 
                        type="checkbox"
                        :checked="form.active"
                        @change="$emit('update:form', { ...form, active: $event.target.checked })"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded transition-colors duration-200"
                    />
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Set as active connection
                    </label>
                </div>
                
                <!-- Modal Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <button 
                        type="button" 
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors duration-200"
                    >
                        <component 
                            :is="isSubmitting ? ArrowPathIcon : CheckIcon" 
                            :class="['w-4 h-4 mr-2', { 'animate-spin': isSubmitting }]" 
                        />
                        {{ isSubmitting ? 'Saving...' : 'Save Connection' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { XMarkIcon, CheckIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

defineProps({
    editing: {
        type: Object,
        default: null
    },
    form: {
        type: Object,
        required: true
    },
    drivers: {
        type: Array,
        required: true
    },
    isSubmitting: {
        type: Boolean,
        default: false
    }
})

defineEmits(['close', 'save', 'update:form'])
</script>