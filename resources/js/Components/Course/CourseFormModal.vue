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

const props = defineProps({
    show: { type: Boolean, default: false },
    mode: { type: String, default: 'create', validator: value => ['create', 'edit'].includes(value) },
    course: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: '2xl' }
});

const emit = defineEmits(['close', 'success']);

// Optimized form configuration with consolidated structure
const FORM_FIELDS = [
    { key: 'title', label: 'Course Title', component: 'TextInput', required: true, placeholder: 'Enter course title', validation: { required: true, minLength: 3 } },
    { key: 'description', label: 'Description', component: 'TextArea', placeholder: 'Enter course description (optional)', rows: 4, validation: { minLength: 10 } },
    { key: 'price', label: 'Price', component: 'TextInput', type: 'number', required: true, step: '0.01', min: '0', validation: { required: true, min: 0 }, editOnly: true },
    { key: 'category_id', label: 'Category', component: 'SelectInput', required: true, validation: { required: true } },
    { key: 'status', label: 'Status', component: 'SelectInput', required: true, validation: { required: true } },
    { key: 'level', label: 'Level', component: 'SelectInput', required: true, validation: { required: true }, editOnly: true },
    { key: 'duration', label: 'Duration (hours)', component: 'TextInput', type: 'number', required: true, min: '1', validation: { required: true, min: 1 }, editOnly: true },
    { key: 'language', label: 'Language', component: 'SelectInput', required: true, validation: { required: true }, editOnly: true },
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
    const defaults = { title: '', description: '', price: '', status: 'draft', category_id: '', level: 'beginner', duration: '', language: 'en', thumbnail: null };
    return props.mode === 'edit' && props.course ? { ...defaults, ...props.course, thumbnail: null } : defaults;
};

const form = useForm(getDefaultFormData());

// Optimized validation state initialization
const validationState = reactive(Object.fromEntries(FORM_FIELDS.map(field => [field.key, { valid: true, message: '' }])));

// Consolidated validation function
const validateField = (fieldKey, value) => {
    const field = FORM_FIELDS.find(f => f.key === fieldKey);
    if (!field?.validation) return true;
    
    const { validation: rules, label } = field;
    const state = validationState[fieldKey];
    
    if (rules.required && (!value || value.toString().trim() === '')) {
        return Object.assign(state, { valid: false, message: `${label} is required` }), false;
    }
    if (rules.minLength && value && value.length < rules.minLength) {
        return Object.assign(state, { valid: false, message: `${label} must be at least ${rules.minLength} characters` }), false;
    }
    if (rules.min !== undefined && Number(value) < rules.min) {
        return Object.assign(state, { valid: false, message: `${label} must be at least ${rules.min}` }), false;
    }
    
    return Object.assign(state, { valid: true, message: '' }), true;
};

// Optimized computed properties
const visibleFields = computed(() => FORM_FIELDS.filter(field => !field.editOnly || props.mode === 'edit'));
const isFormValid = computed(() => visibleFields.value.every(field => !field.validation || validateField(field.key, form[field.key])));

// Optimized helper functions and computed properties
const getFieldOptions = (fieldKey) => fieldKey === 'category_id' 
    ? [{ value: '', label: 'Select a category' }, ...props.categories.map(cat => ({ value: cat.id, label: cat.name }))]
    : FIELD_OPTIONS[fieldKey] || [];

const modalTitle = computed(() => props.title || (props.mode === 'create' ? 'Create Course' : 'Edit Course'));
const submitButtonText = computed(() => {
    const action = props.mode === 'create' ? 'Create' : 'Update';
    return form.processing ? `${action.slice(0, -1)}ing...` : `${action} Course`;
});

// Optimized watchers
watch(() => props.course, (newCourse) => {
    if (props.mode === 'edit' && newCourse) Object.assign(form, getDefaultFormData());
}, { immediate: true });

watch(() => props.show, (show) => {
    if (!show) {
        form.clearErrors();
        Object.keys(validationState).forEach(key => Object.assign(validationState[key], { valid: true, message: '' }));
    }
});

// Optimized form submission and handlers
const submit = () => {
    if (!isFormValid.value) return;
    
    const isCreate = props.mode === 'create';
    const method = isCreate ? 'post' : 'put';
    const routeName = `courses.${isCreate ? 'store' : 'update'}`;
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
    <Modal :show="show" @close="closeModal" :max-width="maxWidth">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                {{ modalTitle }}
            </h2>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div v-for="field in visibleFields" :key="field.key" class="form-field">
                    <InputLabel :for="field.key" :value="field.label" />
                    
                    <component 
                        :is="field.component"
                        :id="field.key"
                        v-model="form[field.key]"
                        v-bind="field"
                        class="mt-1 block w-full"
                        :class="{ 'border-red-500': !validationState[field.key].valid }"
                        @blur="validateField(field.key, form[field.key])"
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
                    
                    <InputError :message="validationState[field.key].message || form.errors[field.key]" class="mt-1" />
                </div>
                
                <div class="flex justify-end mt-6 gap-x-4">
                    <SecondaryButton @click="closeModal" type="button">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="!isFormValid || form.processing" class="flex items-center gap-2">
                        <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                        {{ submitButtonText }}
                    </PrimaryButton>
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