<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('caisse.deposit') }}</h3>
        
        <form @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
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
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
              <select v-model="form.source" required class="form-input">
                <option value="">Sélectionner une source</option>
                <option value="bank_transfer">Virement bancaire</option>
                <option value="cash_delivery">Livraison espèces</option>
                <option value="other">Autre</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="form-input"
                placeholder="Description du dépôt..."
                required
              ></textarea>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              class="btn-secondary"
            >
              {{ $t('common.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary"
            >
              <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              Déposer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close', 'deposit'])

const form = ref({
  amount: 0,
  source: '',
  description: ''
})

const loading = ref(false)

const handleSubmit = () => {
  loading.value = true
  emit('deposit', form.value)
}
</script>
