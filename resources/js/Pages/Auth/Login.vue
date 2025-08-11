<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuntheticationIllustration from "@/Components/AuntheticationIllustration.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",  // Make sure password is initialized
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};

const loginByGoogle = () => {
    try {
        if (!route('google.login')) {
            throw new Error('Google login route not defined');
        }
        window.location.href = route('google.login');
    } catch (error) {
        console.error('Google login error:', error);
        form.errors.email = error.message;
    }
};

// استبدال دالة lang() بالترجمات من Laravel
const __ = (key) => {
  const translations = {
    'Login': 'تسجيل الدخول',
    'Email': 'البريد الإلكتروني',
    'Password': 'كلمة المرور',
    'Remember me': 'تذكرني',
    'Forgot your password?': 'نسيت كلمة المرور؟',
    'Log in': 'تسجيل الدخول'
  };
  return translations[key] || key;
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('Login')" />
        <template #illustration>
            <AuntheticationIllustration type="login" class="w-72 h-auto" />
        </template>
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}


        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="__('Email')" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    :placeholder="__('Email')"
                    :error="form.errors.email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="__('Password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    :error="form.errors.password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span
                        class="ml-2 text-sm text-slate-600 dark:text-slate-400"
                        >{{ __('Remember me') }}</span
                    >
                </label>
            </div>
            <div class="flex items-center justify-between mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-slate-800"
                >
                    {{ __('Forgot your password?') }}
                </Link>

                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? __('Log in') + "..."
                            : __('Log in')
                    }}
                </PrimaryButton>
                <form @submit.prevent="loginByGoogle">
             <DangerButton class="ml-4"  :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing" >
                <span class="ml-2">Login with Google</span>
              </DangerButton>
        </form>
            </div>




        </form>





    </GuestLayout>
</template>
