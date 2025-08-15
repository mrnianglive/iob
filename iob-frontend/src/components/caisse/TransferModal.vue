<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('caisse.transfer') }}</h3>
        
        <form @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Caisse de destination</label>
              <select v-model="form.destinationCaisseId" required class="form-input">
                <option value="">Sélectionner une caisse</option>
                <option v-for="caisse in availableCaisses" :key="caisse.id" :value="caisse.id">
                  {{ caisse.name }}
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
              <textarea
                v-model="form.reason"
                rows="3"
                class="form-input"
                placeholder="Motif du transfert..."
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
              Transférer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close', 'transfer'])

const form = ref({
  destinationCaisseId: '',
  amount: 0,
  reason: ''
})

const availableCaisses = ref([])
const loading = ref(false)

const fetchAvailableCaisses = async () => {
  try {
    const response = await axios.get('/partner-api/caisse/available')
    availableCaisses.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des caisses:', error)
  }
}

const handleSubmit = () => {
  loading.value = true
  emit('transfer', form.value)
}

onMounted(() => {
  fetchAvailableCaisses()
})
</script>
