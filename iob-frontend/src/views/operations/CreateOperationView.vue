<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('operations.create') }}</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Operation Form -->
          <div class="lg:col-span-2">
            <OperationForm 
              v-model="operationData"
              @submit="handleSubmit"
              :loading="isLoading"
              :error="error"
            />
          </div>

          <!-- Bill Breakdown -->
          <div class="lg:col-span-1">
            <BillBreakdown 
              v-if="showBillBreakdown"
              v-model="operationData.billBreakdown"
              :total-amount="operationData.amount"
            />
          </div>
        </div>

        <!-- Approval Workflow -->
        <ApprovalWorkflow 
          v-if="requiresApproval && operationData.id"
          :operation="operationData"
          @approved="handleApproval"
          @rejected="handleRejection"
        />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import OperationForm from '../../components/operations/OperationForm.vue'
import BillBreakdown from '../../components/operations/BillBreakdown.vue'
import ApprovalWorkflow from '../../components/operations/ApprovalWorkflow.vue'
import axios from 'axios'

const router = useRouter()

const operationData = ref({
  type: '',
  amount: 0,
  currency: 'EUR',
  description: '',
  billBreakdown: {}
})

const isLoading = ref(false)
const error = ref(null)

const showBillBreakdown = computed(() => {
  return operationData.value.type && operationData.value.amount > 0
})

const requiresApproval = computed(() => {
  return operationData.value.amount > 10000 // Example threshold
})

const handleSubmit = async (data) => {
  try {
    isLoading.value = true
    error.value = null
    
    const response = await axios.post('/partner-api/operations', data)
    operationData.value = response.data.operation
    
    if (!requiresApproval.value) {
      router.push('/operations')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la création de l\'opération'
  } finally {
    isLoading.value = false
  }
}

const handleApproval = () => {
  router.push('/operations')
}

const handleRejection = () => {
  router.push('/operations')
}
</script>
