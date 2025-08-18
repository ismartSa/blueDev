<!-- resources/js/Pages/Opt/Upload.vue -->

<template>
    <div class="container mx-auto p-6">
      <h1 class="text-3xl font-bold mb-6">JSON to PostgreSQL Converter</h1>

      <!-- Success Message -->
      <div v-if="$page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <!-- Error Message -->
      <div v-if="$page.props.flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>

      <!-- Converter Form -->
      <form @submit.prevent="submit" class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-6">

        <!-- JSON Input -->
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">
            JSON Content:
          </label>
          <textarea
            v-model="form.json_content"
            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            rows="15"
            placeholder="Paste your JSON content here..."
            required
          ></textarea>
          <div v-if="form.errors.json_content" class="text-red-500 text-xs mt-1">
            {{ form.errors.json_content }}
          </div>
          <p class="text-gray-600 text-xs mt-1">Paste JSON array or object to convert to PostgreSQL INSERT statements</p>
        </div>

        <!-- Table Name Input -->
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">
            Table Name:
          </label>
          <input
            type="text"
            v-model="form.table_name"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            placeholder="Enter PostgreSQL table name"
            required
          >
          <div v-if="form.errors.table_name" class="text-red-500 text-xs mt-1">
            {{ form.errors.table_name }}
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
          >
            <span v-if="form.processing">Converting...</span>
            <span v-else>Convert to SQL</span>
          </button>

          <button
            type="button"
            @click="clearForm"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
          >
            Clear
          </button>
        </div>
      </form>

      <!-- SQL Output -->
      <div v-if="$page.props.flash.sql_output" class="bg-gray-900 text-green-400 p-6 rounded-lg">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-white">Generated PostgreSQL:</h3>
          <button
            @click="copySql"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm"
          >
            Copy SQL
          </button>
        </div>
        <pre class="text-sm overflow-x-auto whitespace-pre-wrap">{{ $page.props.flash.sql_output }}</pre>
      </div>

      <!-- Instructions -->
      <div class="bg-blue-50 border border-blue-200 p-4 rounded mt-6">
        <h3 class="font-bold mb-2 text-blue-800">How to use:</h3>
        <ul class="list-disc list-inside text-sm text-blue-700">
          <li>Paste your JSON content in the textarea above</li>
          <li>Enter the PostgreSQL table name</li>
          <li>Click "Convert to SQL" to generate PostgreSQL queries</li>
          <li>Copy the generated SQL and run it in your PostgreSQL database</li>
        </ul>
      </div>

      <!-- Example -->
      <div class="bg-yellow-50 border border-yellow-200 p-4 rounded mt-4">
        <h3 class="font-bold mb-2 text-yellow-800">Example JSON:</h3>
        <pre class="text-sm text-yellow-700 overflow-x-auto">
  [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    }
  ]
        </pre>
      </div>
    </div>
  </template>

  <script>
  import { useForm } from '@inertiajs/vue3'

  export default {
    setup() {
      const form = useForm({
        json_content: '',
        table_name: ''
      })

      const submit = () => {
        form.post('/opt/convert')
      }

      const clearForm = () => {
        form.json_content = ''
        form.table_name = ''
      }

      const copySql = () => {
        const sqlOutput = document.querySelector('pre').textContent
        navigator.clipboard.writeText(sqlOutput).then(() => {
          alert('SQL copied to clipboard!')
        })
      }

      return {
        form,
        submit,
        clearForm,
        copySql
      }
    }
  }
  </script>
