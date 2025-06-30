// ... existing code ...
            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                <template #icon>
                    <HomeIcon class="w-5 h-5" />
                </template>
                Dashboard
            </NavLink>

            <!-- Quizzes Link -->
            <NavLink v-if="$page.props.auth.user.permissions.includes('YOUR_PERMISSION_STRING_HERE')" :href="route('YOUR_ROUTE_NAME_HERE')" :active="route().current('YOUR_ROUTE_NAME_HERE') || route().current('YOUR_ROUTE_NAME_HERE.*')">
                <template #icon>
                    <!-- Replace YOUR_ICON_COMPONENT_NAME_HERE with your actual icon component -->
                    <YOUR_ICON_COMPONENT_NAME_HERE class="w-5 h-5" />
                </template>
                Quizzes
            </NavLink>

            <NavLink :href="route('users.index')" :active="route().current('users.index') || route().current('users.create') || route().current('users.edit') "
                v-if="$page.props.auth.user.permissions.includes('view users')">
// ... existing code ...
