<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-2xl shadow-lg rounded-md bg-white max-w-2xl">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('remittance.create') }}</h3>
        
        <form @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('remittance.source') }}</label>
              <select v-model="form.sourceAgencyId" required class="form-input">
                <option value="">Sélectionner une agence</option>
                <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                  {{ agency.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('remittance.destination') }}</label>
              <select v-model="form.destinationAgencyId" required class="form-input">
                <option value="">Sélectionner une agence</option>
                <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                  {{ agency.name }}
                </option>
              </select>
            </div>
            
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Devise</label>
              <select v-model="form.currency" required class="form-input">
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="form-input"
                placeholder="Description de la remise..."
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
              Créer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import axios from 'axios'

const emit = defineEmits(['close', 'created'])

const form = ref({
  sourceAgencyId: '',
  destinationAgencyId: '',
  amount: 0,
  currency: 'EUR',
  description: ''
})

const agencies = ref([])
const loading = ref(false)
const error = ref(null)

const fetchAgencies = async () => {
  try {
    const response = await axios.get('/partner-api/agencies')
    agencies.value = response.data
  } catch (err) {
    console.error('Erreur lors du chargement des agences:', err)
  }
}

const handleSubmit = async () => {
  try {
    loading.value = true
    error.value = null
    
    await axios.post('/partner-api/remittance', form.value)
    emit('created')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la création de la remise'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAgencies()
})
</script>
