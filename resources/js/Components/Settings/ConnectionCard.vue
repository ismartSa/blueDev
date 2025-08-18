<template>
    <div :class="cardClasses">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <StatusIndicator :active="connection.active" />
                <ConnectionInfo 
                    :name="connection.name" 
                    :driver="connection.driver" 
                />
            </div>
            <div class="flex items-center space-x-3">
                <StatusBadge 
                    :active="connection.active" 
                    :response-time="connection.response_time" 
                />
                <EditButton @click="$emit('edit', connection)" />
            </div>
        </div>
        
        <!-- Connection Details -->
        <ConnectionDetails 
            :host="connection.host" 
            :database="connection.database" 
        />
        
        <!-- Error Message -->
        <ErrorMessage 
            v-if="!connection.active && connection.message" 
            :message="connection.message" 
        />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { PencilIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    connection: {
        type: Object,
        required: true
    }
})

defineEmits(['edit'])

const cardClasses = computed(() => {
    return 'bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5'
})
</script>

<!-- Inline Components for Better Performance -->
<script>
// Status Indicator Component
const StatusIndicator = {
    props: ['active'],
    template: `
        <div :class="[
            'w-3 h-3 rounded-full transition-colors duration-200',
            active ? 'bg-green-500 shadow-green-500/50 shadow-sm' : 'bg-red-500 shadow-red-500/50 shadow-sm'
        ]"></div>
    `
}

// Connection Info Component
const ConnectionInfo = {
    props: ['name', 'driver'],
    template: `
        <div>
            <h4 class="font-medium text-gray-900 dark:text-white">{{ name }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ driver.toUpperCase() }}</p>
        </div>
    `
}

// Status Badge Component
const StatusBadge = {
    props: ['active', 'responseTime'],
    template: `
        <div class="text-right">
            <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200',
                active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
            ]">
                {{ active ? 'Connected' : 'Disconnected' }}
            </span>
            <div v-if="responseTime" class="text-xs text-gray-500 mt-1 font-mono">
                {{ responseTime }}ms
            </div>
        </div>
    `
}

// Edit Button Component
const EditButton = {
    components: { PencilIcon },
    template: `
        <button 
            @click="$emit('click')"
            class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-all duration-200"
            title="Edit Connection"
        >
            <PencilIcon class="w-4 h-4" />
        </button>
    `,
    emits: ['click']
}

// Connection Details Component
const ConnectionDetails = {
    props: ['host', 'database'],
    template: `
        <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
            <DetailItem label="Host" :value="host" />
            <DetailItem label="Database" :value="database" />
        </div>
    `,
    components: {
        DetailItem: {
            props: ['label', 'value'],
            template: `
                <div>
                    <span class="text-gray-500 dark:text-gray-400">{{ label }}:</span>
                    <span class="ml-1 text-gray-900 dark:text-white font-mono text-xs">{{ value }}</span>
                </div>
            `
        }
    }
}

// Error Message Component
const ErrorMessage = {
    props: ['message'],
    template: `
        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-300">{{ message }}</p>
        </div>
    `
}

export default {
    components: {
        StatusIndicator,
        ConnectionInfo,
        StatusBadge,
        EditButton,
        ConnectionDetails,
        ErrorMessage
    }
}
</script>