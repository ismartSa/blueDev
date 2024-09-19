<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    section: Object,
});

const form = useForm({
    title: props.section.title,
    order: props.section.order,
});

function submit() {
    form.put(`/sections/${props.section.id}`);
}
</script>

<template>
    <div>
        <h1>Edit Section</h1>

        <form @submit.prevent="submit">
            <div>
                <label for="title">Title</label>
                <input v-model="form.title" id="title" type="text" />
            </div>

            <div>
                <label for="order">Order</label>
                <input v-model="form.order" id="order" type="number" />
            </div>

            <button type="submit">Save</button>
        </form>
    </div>
</template>
