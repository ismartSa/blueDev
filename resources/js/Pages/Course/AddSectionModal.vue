<template>
    <section class="space-y-6">
     <Modal :show="props.show" @close="emit('close')">
     <form class="p-6" @submit.prevent="create">
     <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100" > Add New Section</h2>
        <div class="my-6 space-y-4">
             <div>
                <InputLabel for="title" :value="lang().label.title" />
                <TextInput
                    id="title"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.title"
                    required
                    :placeholder="lang().placeholder.title"
                    :error="form.errors.title" />
                    <InputError class="mt-2" :message="form.errors.name" />
               </div>
               <div class="flex justify-end">
                    <SecondaryButton
                        :disabled="form.processing"
                        @click="emit('close')"
                    >
                        {{ lang().button.close }}
                    </SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="create"
                    >
                        {{
                            form.processing
                                ? lang().button.add + "..."
                                : lang().button.add
                        }}
                    </PrimaryButton>
                </div>
        </div>
            </form>
        </Modal>
    </section>

  </template>

  <script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { watchEffect } from "vue";


  const props = defineProps({
    courseId: Number,
  });

  const emit = defineEmits(["close"]);
  const form = useForm({
    title: '',
  });


  const create = () => {
        form.post(route("course.store"), {
            preserveScroll: true,
            onSuccess: () => {
                emit("close");
                form.reset();
            },
            onError: () => null,
            onFinish: () => null,
        });
};

  </script>
