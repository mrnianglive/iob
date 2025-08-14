<template>
  <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
    <div class="rounded-md shadow-sm -space-y-px">
      <div>
        <label for="email" class="sr-only">{{ $t('auth.email') }}</label>
        <input
          id="email"
          v-model="form.email"
          name="email"
          type="email"
          autocomplete="email"
          required
          class="form-input relative block w-full rounded-t-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
          :placeholder="$t('auth.email')"
        />
      </div>
      <div>
        <label for="password" class="sr-only">{{ $t('auth.password') }}</label>
        <input
          id="password"
          v-model="form.password"
          name="password"
          type="password"
          autocomplete="current-password"
          required
          class="form-input relative block w-full rounded-b-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
          :placeholder="$t('auth.password')"
        />
      </div>
    </div>

    <div v-if="error" class="rounded-md bg-red-50 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            {{ error }}
          </h3>
        </div>
      </div>
    </div>

    <div>
      <button
        type="submit"
        :disabled="loading"
        class="group relative flex w-full justify-center rounded-md bg-primary-600 py-2 px-3 text-sm font-semibold text-white hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-50"
      >
        <span v-if="loading" class="absolute inset-y-0 left-0 flex items-center pl-3">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
        </span>
        {{ $t('auth.loginButton') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  loading: Boolean,
  error: String
})

const emit = defineEmits(['submit'])

const form = ref({
  email: '',
  password: ''
})

const handleSubmit = () => {
  emit('submit', form.value)
}
</script>
