<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ $t('auth.twoFactor') }}
        </h3>
        <p class="text-sm text-gray-500 mb-6">
          {{ $t('auth.enterCode') }}
        </p>
        
        <form @submit.prevent="handleSubmit">
          <input
            v-model="code"
            type="text"
            maxlength="6"
            class="form-input w-full text-center text-2xl tracking-widest mb-4"
            placeholder="000000"
            autocomplete="one-time-code"
            required
          />
          
          <div class="flex gap-3">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
            >
              {{ $t('common.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="loading || code.length !== 6"
              class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50 transition-colors"
            >
              <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ $t('common.verify') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  loading: Boolean
})

const emit = defineEmits(['verify', 'close'])

const code = ref('')

const handleSubmit = () => {
  if (code.value.length === 6) {
    emit('verify', code.value)
  }
}
</script>
