<template>
  <div class="card">
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('operations.type') }}</label>
          <select v-model="form.type" required class="form-input">
            <option value="">Sélectionner un type</option>
            <option value="deposit">Dépôt</option>
            <option value="withdrawal">Retrait</option>
            <option value="transfer">Transfert</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('operations.currency') }}</label>
          <select v-model="form.currency" required class="form-input">
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
            <option value="GBP">GBP</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('operations.amount') }}</label>
          <input
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0"
            required
            class="form-input"
            placeholder="0.00"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="form-input"
            placeholder="Description de l'opération..."
          ></textarea>
        </div>
      </div>

      <div v-if="error" class="mt-4 rounded-md bg-red-50 p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end space-x-3">
        <router-link
          to="/operations"
          class="btn-secondary"
        >
          {{ $t('common.cancel') }}
        </router-link>
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary"
        >
          <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
          {{ $t('common.save') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: Object,
  loading: Boolean,
  error: String
})

const emit = defineEmits(['update:modelValue', 'submit'])

const form = ref({ ...props.modelValue })

watch(() => props.modelValue, (newValue) => {
  form.value = { ...newValue }
}, { deep: true })

watch(form, (newValue) => {
  emit('update:modelValue', newValue)
}, { deep: true })

const handleSubmit = () => {
  emit('submit', form.value)
}
</script>
