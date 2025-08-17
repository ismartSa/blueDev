<script setup>
import { reactive, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import FileInput from '@/Components/FileInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    show: { type: Boolean, default: false },
    mode: { type: String, default: 'create', validator: value => ['create', 'edit'].includes(value) },
    course: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: '2xl' },
    closeable: { type: Boolean, default: true }
});

const emit = defineEmits(['close', 'success']);

// Optimized form configuration with consolidated structure
const FORM_FIELDS = [
    { key: 'title', label: 'Course Title', component: 'TextInput', required: true, placeholder: 'Enter course title', validation: { required: true, minLength: 3 } },
    { key: 'description', label: 'Description', component: 'TextArea', placeholder: 'Enter course description (optional)', rows: 4, validation: { minLength: 10 } },
    { key: 'price', label: 'Price', component: 'TextInput', type: 'number', required: false, step: '0.01', min: '0', validation: { min: 0 }, editOnly: true },
    { key: 'category_id', label: 'Category', component: 'SelectInput', required: false, validation: {} },
    { key: 'status', label: 'Status', component: 'SelectInput', required: true, validation: { required: true } },
    { key: 'level', label: 'Level', component: 'SelectInput', required: false, validation: {}, editOnly: true },
    { key: 'duration', label: 'Duration (hours)', component: 'TextInput', type: 'number', required: false, min: '1', validation: { min: 1 }, editOnly: true },
    { key: 'language', label: 'Language', component: 'SelectInput', required: false, validation: {}, editOnly: true },
    { key: 'thumbnail', label: 'Thumbnail (optional)', component: 'FileInput', accept: 'image/*', editOnly: true }
];

// Consolidated field options
const FIELD_OPTIONS = {
    status: [{ value: 'draft', label: 'Draft' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
    level: [{ value: 'beginner', label: 'Beginner' }, { value: 'intermediate', label: 'Intermediate' }, { value: 'advanced', label: 'Advanced' }],
    language: [{ value: 'en', label: 'English' }, { value: 'ar', label: 'Arabic' }, { value: 'fr', label: 'French' }, { value: 'es', label: 'Spanish' }]
};

// Optimized form data initialization
const getDefaultFormData = () => {
    const defaults = { 
        title: '', 
        description: '', 
        price: '', 
        status: 'draft', 
        category_id: '', 
        level: '', 
        duration: '', 
        language: '', 
        thumbnail: null 
    };
    return defaults;
};

const form = useForm(getDefaultFormData());

// Optimized validation state initialization
const validationState = reactive(Object.fromEntries(FORM_FIELDS.map(field => [field.key, { valid: true, message: '' }])));

// Consolidated validation function
const validateField = (fieldKey, value) => {
    const field = FORM_FIELDS.find(f => f.key === fieldKey);
    if (!field?.validation) {
        validationState[fieldKey] = { valid: true, message: '' };
        return true;
    }
    
    const { validation: rules, label } = field;
    let isValid = true, message = '';
    
    // Check required fields
    if (rules.required && (!value || value.toString().trim() === '')) {
        isValid = false;
        message = `${label} is required`;
    }
    // Check minimum length for strings
    else if (rules.minLength && value && value.toString().length < rules.minLength) {
        isValid = false;
        message = `${label} must be at least ${rules.minLength} characters`;
    }
    // Check minimum value for numbers
    else if (rules.min !== undefined && value !== '' && value !== null && parseFloat(value) < rules.min) {
        isValid = false;
        message = `${label} must be at least ${rules.min}`;
    }
    
    validationState[fieldKey] = { valid: isValid, message };
    return isValid;
};

// Enhanced computed properties for better organization
const basicFields = computed(() => FORM_FIELDS.filter(field => 
    !field.editOnly
));

const advancedFields = computed(() => FORM_FIELDS.filter(field => 
    field.editOnly && props.mode === 'edit'
));

const visibleFields = computed(() => FORM_FIELDS.filter(field => !field.editOnly || props.mode === 'edit'));
const isFormValid = computed(() => {
    // Check required fields only
    const requiredFields = visibleFields.value.filter(field => field.validation?.required);
    return requiredFields.every(field => {
        const value = form[field.key];
        return value !== null && value !== undefined && value !== '';
    });
});

// Optimized helper functions and computed properties
const getFieldOptions = (fieldKey) => {
    if (fieldKey === 'category_id') {
        return [{ value: '', label: 'Select a category' }, ...props.categories.map(cat => ({ value: cat.id, label: cat.name }))];
    }
    if (FIELD_OPTIONS[fieldKey]) {
        return [{ value: '', label: `Select ${fieldKey.replace('_', ' ')}` }, ...FIELD_OPTIONS[fieldKey]];
    }
    return [];
};

const modalTitle = computed(() => props.title || (props.mode === 'create' ? 'Create Course' : 'Edit Course'));
const submitButtonText = computed(() => {
    const action = props.mode === 'create' ? 'Create' : 'Update';
    return form.processing ? `${action.slice(0, -1)}ing...` : `${action} Course`;
});

// Optimized watchers
watch(() => props.course, (newCourse) => {
    if (props.mode === 'edit' && newCourse) {
        const defaultData = getDefaultFormData();
        Object.keys(defaultData).forEach(key => {
            form[key] = newCourse[key] !== undefined ? newCourse[key] : defaultData[key];
        });
        form.clearErrors();
        // Clear validation state
        Object.keys(validationState).forEach(key => {
            validationState[key] = { valid: true, message: '' };
        });
    }
}, { immediate: true });

watch(() => props.show, (show) => {
    if (show) {
        if (props.mode === 'create') {
            form.reset();
            const defaultData = getDefaultFormData();
            Object.keys(defaultData).forEach(key => {
                form[key] = defaultData[key];
            });
        } else if (props.mode === 'edit' && props.course) {
            // Ensure edit mode gets fresh course data
            const defaultData = getDefaultFormData();
            Object.keys(defaultData).forEach(key => {
                form[key] = props.course[key] !== undefined ? props.course[key] : defaultData[key];
            });
        }
        // Clear validation state when opening
        Object.keys(validationState).forEach(key => {
            validationState[key] = { valid: true, message: '' };
        });
        form.clearErrors();
    }
});

// Clear validation state when modal closes
watch(() => props.show, (show) => {
    if (!show) {
        form.clearErrors();
        Object.keys(validationState).forEach(key => {
            validationState[key].valid = true;
            validationState[key].message = '';
        });
    }
}, { flush: 'post' });

// Optimized form submission and handlers
const submit = () => {
    if (!isFormValid.value) return;
    
    const isCreate = props.mode === 'create';
    const method = isCreate ? 'post' : 'put';
    const routeName = `dashboard.courses.${isCreate ? 'store' : 'update'}`;
    const routeParams = isCreate ? [] : [props.course.id];
    
    form[method](route(routeName, ...routeParams), {
        onSuccess: () => emit('success', `Course ${isCreate ? 'created' : 'updated'} successfully!`),
        onError: (errors) => {
            console.error('Form errors:', errors);
            Object.keys(errors).forEach(field => {
                if (validationState[field]) Object.assign(validationState[field], { valid: false, message: errors[field] });
            });
        }
    });
};

const closeModal = () => emit('close');
const handleFileChange = (event) => { if (event.target.files[0]) form.thumbnail = event.target.files[0]; };
</script>

<template>
    <Modal :show="show" @close="closeModal" :max-width="maxWidth" :closeable="closeable">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl">
            <!-- Enhanced Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ modalTitle }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ mode === 'create' ? 'Fill in the details to create a new course' : 'Update the course information below' }}
                    </p>
                </div>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            
            <!-- Enhanced Form with Better Layout -->
            <form @submit.prevent="submit" class="p-6">
                <div class="space-y-6">
                    <!-- Basic Information Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Basic Information
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div v-for="field in basicFields" :key="field.key" class="form-field">
                                <InputLabel :for="field.key" :value="field.label" class="font-medium" />
                                
                                <component 
                                    :is="field.component"
                                    :id="field.key"
                                    v-model="form[field.key]"
                                    v-bind="field"
                                    class="mt-2 block w-full transition-all duration-200"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': !validationState[field.key].valid,
                                        'border-gray-300 focus:border-blue-500 focus:ring-blue-500': validationState[field.key].valid
                                    }"
                                    @blur="validateField(field.key, form[field.key])"
                                    @input="validateField(field.key, form[field.key])"
                                >
                                    <option 
                                        v-if="field.component === 'SelectInput'"
                                        v-for="option in getFieldOptions(field.key)" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </component>
                                
                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 transform -translate-y-1"
                                    enter-to-class="opacity-100 transform translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 transform translate-y-0"
                                    leave-to-class="opacity-0 transform -translate-y-1"
                                >
                                    <InputError 
                                        v-if="validationState[field.key].message || form.errors[field.key]"
                                        :message="validationState[field.key].message || form.errors[field.key]" 
                                        class="mt-2" 
                                    />
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings Section (Edit Mode Only) -->
                    <div v-if="mode === 'edit'" class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Course Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="field in advancedFields" :key="field.key" class="form-field">
                                <InputLabel :for="field.key" :value="field.label" class="font-medium" />
                                
                                <component 
                                    :is="field.component"
                                    :id="field.key"
                                    v-model="form[field.key]"
                                    v-bind="field"
                                    class="mt-2 block w-full transition-all duration-200"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': !validationState[field.key].valid,
                                        'border-gray-300 focus:border-blue-500 focus:ring-blue-500': validationState[field.key].valid
                                    }"
                                    @blur="validateField(field.key, form[field.key])"
                                    @input="field.component !== 'FileInput' ? validateField(field.key, form[field.key]) : null"
                                    @change="field.component === 'FileInput' ? handleFileChange($event) : null"
                                >
                                    <option 
                                        v-if="field.component === 'SelectInput'"
                                        v-for="option in getFieldOptions(field.key)" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </component>
                                
                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 transform -translate-y-1"
                                    enter-to-class="opacity-100 transform translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 transform translate-y-0"
                                    leave-to-class="opacity-0 transform -translate-y-1"
                                >
                                    <InputError 
                                        v-if="validationState[field.key].message || form.errors[field.key]"
                                        :message="validationState[field.key].message || form.errors[field.key]" 
                                        class="mt-2" 
                                    />
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Footer with Better Actions -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span v-if="mode === 'edit'" class="flex items-center gap-2">
                            <CheckCircleIcon class="w-4 h-4 text-green-500" />
                            Last updated: {{ course?.updated_at || 'Never' }}
                        </span>
                    </div>
                    
                    <div class="flex gap-3">
                        <SecondaryButton 
                            @click="closeModal" 
                            type="button"
                            class="px-6 py-2"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            type="submit" 
                            :disabled="!isFormValid || form.processing" 
                            class="flex items-center gap-2 px-6 py-2 min-w-[120px] justify-center"
                            :class="{
                                'opacity-50 cursor-not-allowed': !isFormValid || form.processing,
                                'hover:shadow-lg transform hover:scale-105': isFormValid && !form.processing
                            }"
                        >
                            <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            <CheckCircleIcon v-else class="w-4 h-4" />
                            {{ submitButtonText }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>

<style scoped>
.form-field {
    @apply space-y-1;
}
</style>