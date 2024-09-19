    <template>
        <Head title="تفاصيل الدورة" />

        <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
            <div class="rounded-lg overflow-hidden w-fit">
                <!-- Image -->
                <img :src="course.image" alt="صورة الدورة" class="w-full h-64 object-cover" />
            </div>
            </div>
            <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <div class="flex justify-between p-2">
                <!-- Title -->
                <h2 class="text-xl font-bold">{{ course.title }}</h2>
                <!-- Enroll Button -->
                <PrimaryButton @click="enroll" :disabled="enrolled">
                {{ enrolled ? 'enrolled' : 'enroll Now' }}
                </PrimaryButton>
                <!-- Button ADD-->
                <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <PrimaryButton
                        v-show="can(['create course'])"
                        class="rounded-none"
                        @click="data.createOpen = true"
                    >
                    Add Section
                    </PrimaryButton>
                    <Add
                        :show="data.createOpen"
                        @close="data.createOpen = false"
                        :title="props.title"
                        :courseId="props.course.id"
                    />
                </div>
                </div>
                <!-- Button end ADD-->


             <!-- Create Lecture Button -->
          <Link :href="route('course.create.lecture', { courseId: course.id })" class="ml-2">
            <PrimaryButton>Add Lecture</PrimaryButton>
          </Link>
            </div>
            <div class="p-4">
                <!-- Description -->
                <p class="text-gray-600">{{ course.description }}</p>

                <!-- Instructor -->
                <h3 class="text-lg font-bold mt-4">Instructor</h3>
                <p class="text-gray-600">{{ course.name }}</p>
                <!-- Prerequisites -->
                <h3 class="text-lg font-bold mt-4">Prerequisites</h3>
                <ul class="list-disc ml-4">
                <li v-for="(prerequisite, index) in course.prerequisites" :key="index">
                    {{ prerequisite }}
                </li>
                </ul>
                <!-- Learning Outcomes -->
                <h3 class="text-lg font-bold mt-4">Learning Outcomes</h3>
                <ul class="list-disc ml-4">
                <li v-for="(outcome, index) in course.learningOutcomes" :key="index">
                    {{ outcome }}
                </li>
                </ul>
                <!-- Sections and Lectures -->
                <h3 class="text-lg font-bold mt-4">Course Content</h3>
               <div v-for="section in sections.sort((a, b) => a.order - b.order)" :key="section.id" class="mt-8">
                <Section :section="section" :lectures="lectures.filter(lecture => lecture.section_id === section.id)" />
            </div>
            </div>
            </div>
        </div>

        <AddSectionModal
            v-if="data.addSectionOpen"
             @close="data.addSectionOpen = false"
             @add-section="addSection" :courseId="course.id"
             />
        </AuthenticatedLayout>
    </template>

    <script setup>
    import { Head, Link } from "@inertiajs/vue3";
    import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
    import Breadcrumb from "@/Components/Breadcrumb.vue";
    import PrimaryButton from "@/Components/PrimaryButton.vue";
    import { usePage } from "@inertiajs/vue3";
    import {reactive, ref } from "vue";
    import Section from '@/Pages/Course/Section.vue';
    import Lecture from '@/Pages/Course/Lecture.vue';
    import AddSectionModal from '@/Pages/Course/AddSectionModal.vue';
    import Add from '@/Pages/Course/Add.vue';


const props = defineProps({
        title: String,
        course: Object,
        sections: Array,
        lectures: Array,
        breadcrumbs: Object,
        user: Object,
 });

 const enrolled = ref(false);
const data = reactive({
    params: {

    },
  addSectionOpen: false,
  createOpen:false,

});

const showAddSectionModal = ref(false);

const enroll = async () => {
    try {
        // 1. Send a POST request to the Laravel endpoint using Inertia.js
        await usePage().post(`/courses/${props.course.id}/enroll`, {
            userId: props.user.id,
        });

        // 2. Update the enrolled state
        enrolled.value = true;

        // 3. Optionally, provide feedback to the user
        // For example, display a success message or update the button text
    } catch (error) {
        // Handle errors
        console.error('Enrollment failed:', error);
        // Display an error message to the user
    }
    };

    </script>
