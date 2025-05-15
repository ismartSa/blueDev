<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import NavbarVue from "@/Components/Navbar.vue";
import SideBarVue from "@/Components/SideBar.vue";
import Toast from "@/Components/Toast.vue";
import Footer from "@/Components/Footer.vue";

const page = usePage();
const sidebarOpened = ref(false);

// Handle flash messages
const flash = computed(() => page.props.flash || {
    success: null,
    error: null,
    warning: null,
    info: null
});

// Handle sidebar toggle
const toggleSidebar = () => {
    sidebarOpened.value = !sidebarOpened.value;
};
</script>

<template>
    <div class="flex w-full overflow-hidden">
        <SideBarVue
            :open="sidebarOpened"
            @close="sidebarOpened = false"
        />
        <div class="pl-0 lg:pl-64 w-full min-h-screen block bg-slate-100 dark:bg-slate-900">
            <Toast :flash="flash" />
            <NavbarVue
                :open="sidebarOpened"
                @open="toggleSidebar"
            />
            <!-- Page Content -->
            <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-10 text-slate-900 dark:text-slate-100 text-sm">
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
