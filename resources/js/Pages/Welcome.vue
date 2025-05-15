<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";
import { computed } from 'vue';

// تعريف Props
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    courses: {
        type: [Array, Object],
        default: () => []
    }
});

// تعريف الترجمات
const translations = computed(() => ({
    label: {
        welcome: 'Welcome',
        dashboard: 'Dashboard',
        login: 'Login',
        register: 'Register',
        explore_courses: 'Explore Our Courses',
        view_details: 'View Details'
    }
}));

const lang = () => translations.value;
</script>

<template>
    <Head :title="lang().label.welcome" />
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- الهيدر -->
        <header class="bg-white dark:bg-slate-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <ApplicationLogo class="h-10 w-auto text-primary fill-current" />
                        <p class="text-2xl ml-4 text-primary">{{ $page.props.app.name }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <SwitchLangNavbar />
                        <SwitchDarkMode />
                        <div v-if="canLogin" class="flex items-center space-x-4">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                                class="btn-primary">{{ lang().label.dashboard }}</Link>
                            <template v-else>
                                <Link :href="route('login')" class="btn-secondary">{{ lang().label.login }}</Link>
                                <Link v-if="canRegister" :href="route('register')"
                                    class="btn-primary">{{ lang().label.register }}</Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- القسم الرئيسي -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- قسم البحث -->
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                    {{ lang().label.explore_courses }}
                </h1>
                <div class="max-w-xl mx-auto">
                    <input type="text" placeholder="ابحث عن الدورات..."
                        class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" />
                </div>
            </div>

            <!-- عرض الكورسات -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="course in courses" :key="course.id"
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <img :src="course.thumbnail" :alt="course.title" class="w-full h-48 object-cover" />
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
                            {{ course.title }}
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 mb-4">
                            {{ course.description }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-primary font-bold">{{ course.price }}</span>
                            <Link :href="route('courses.details', { id: course.id, courseSlug: course.slug })"
                                class="btn-primary">
                                {{ lang().label.view_details }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- الفوتر -->
        <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="sm:flex justify-between items-center">
                    <div class="text-center sm:text-left mb-4 sm:mb-0">
                        <p class="text-slate-600 dark:text-slate-400">
                            {{ $page.props.app.name }} ©️ {{ new Date().getFullYear() }}
                        </p>
                    </div>
                    <div class="text-center sm:text-right">
                        <p class="text-slate-500">
                            Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.btn-primary {
    @apply px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors duration-200;
}

.btn-secondary {
    @apply px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary/10 transition-colors duration-200;
}
</style>
