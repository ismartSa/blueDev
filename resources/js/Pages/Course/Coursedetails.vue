<template>

<AuthenticatedLayout>
     <!-- <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" /> -->
     <div class="max-w-6xl mx-auto p-8">
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="flex">
      <div class="w-2/3 p-6">
        <div class="flex items-center mb-4">
          <img src="https://via.placeholder.com/50" alt="Instructor" class="rounded-full mr-4">
          <div>
            <h2 class="text-lg font-semibold">Nicole Brown</h2>
            <p class="text-sm text-gray-600">UX/UI Designer</p>
          </div>
        </div>
        <h1 class="text-3xl font-bold mb-2">The Complete Web Developer Course 2.0</h1>
        <div class="flex items-center mb-4">
          <span class="text-yellow-500">★ ★ ★ ★ ☆</span>
          <span class="ml-2 text-gray-600">(15 reviews)</span>
        </div>
        <p class="text-gray-600 mb-4">Learn Web Development by building 25 websites and mobile apps using HTML, CSS, Javascript, PHP, Python, MySQL & more!</p>
        <div class="flex items-center mb-4">
          <span class="bg-orange-200 text-orange-800 px-2 py-1 rounded-full text-xs font-semibold">WEB DEVELOPMENT</span>
        </div>
        <div class="flex items-center mb-4">
          <span class="text-gray-600 text-sm">12+ Lessons</span>
          <span class="mx-2">|</span>
          <span class="text-gray-600 text-sm">9hr 30min</span>
          <span class="mx-2">|</span>
          <span class="text-gray-600 text-sm">32 students enrolled</span>
        </div>
        <div class="flex items-center mb-4">
          <span class="text-lg font-semibold text-green-600">FREE</span>
          <span class="text-sm text-gray-500 line-through ml-2">$99.00</span>
          <span class="text-sm text-red-600 ml-2">50% off</span>
        </div>
        <div class="flex space-x-4 mb-4">
          <button class="bg-green-500 text-white px-4 py-2 rounded">Enroll Now</button>
          <button class="bg-gray-200 text-gray-700 px-4 py-2 rounded">Add to Wishlist</button>
        </div>
        <div class="bg-gray-100 p-4 rounded mb-4">
          <h2 class="text-xl font-bold mb-2">Overview</h2>
          <p class="text-gray-600 mb-2">Lorem ipsum is simply dummy text of the printing and typesetting industry.</p>
          <h3 class="text-lg font-semibold mb-2">What you'll learn</h3>
          <ul class="list-disc list-inside text-gray-600">
            <li>Become a UX designer.</li>
            <li>Build & test a full website design.</li>
            <li>All the techniques used by UX professionals.</li>
          </ul>
        </div>
      </div>
      <div class="w-1/3 bg-gray-50 p-6">
        <h2 class="text-xl font-bold mb-4">Includes</h2>
        <ul class="text-sm text-gray-700 mb-4">
          <li class="mb-2">11 hours on-demand video</li>
          <li class="mb-2">69 downloadable resources</li>
          <li class="mb-2">Full lifetime access</li>
          <li class="mb-2">Access on mobile and TV</li>
          <li class="mb-2">Assignments</li>
          <li>Certificate of Completion</li>
        </ul>
      </div>
    </div>
    <div class="bg-gray-100 p-4 mt-4 rounded">
      <h2 class="text-xl font-bold mb-4">Course Content</h2>
      <ul class="text-sm text-gray-700">
        <li class="mb-2">
          <button  class="w-full text-left focus:outline-none flex items-center bg-blue-100 p-2 rounded">
            <span class="flex-grow">The Brief</span>
            <span>▼</span>
            <span>▲</span>
          </button>
          <div  class="pl-4 mt-2">
            <p class="text-gray-600">Detailed information about The Brief.</p>
          </div>
        </li>
        <li class="mb-2">
          <button  class="w-full text-left focus:outline-none flex items-center bg-blue-100 p-2 rounded">
            <span class="flex-grow">Wireframing Low Fidelity</span>
            <span>▼</span>
            <span>▲</span>
          </button>
          <div class="pl-4 mt-2">
            <p class="text-gray-600">Detailed information about Wireframing Low Fidelity.</p>
          </div>
        </li>
        <li class="mb-2">
          <button class="w-full text-left focus:outline-none flex items-center bg-blue-100 p-2 rounded">
            <span class="flex-grow">Type, Color & Icon Introduction</span>
            <span>▼</span>
            <span>▲</span>
          </button>
          <div  class="pl-4 mt-2">
            <p class="text-gray-600">Detailed information about Type, Color & Icon Introduction.</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>
</AuthenticatedLayout>
</template>


<script>

import Breadcrumb from "@/Components/Breadcrumb.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { usePage } from "@inertiajs/vue3";
import {reactive, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";


export default {
    props: {
      course: Object,
      title: String,
      breadcrumbs:String,
    },
    data() {
      return {
        comment: {
          name: '',
          email: '',
          subject: '',
          content: '',
        },
      };
    },
    methods: {


      postComment() {
        Inertia.post(`/courses/${this.course.id}/comments`, this.comment);
        this.comment = {
          name: '',
          email: '',
          subject: '',
          content: '',
        };
      },
    },
  };
  </script>


