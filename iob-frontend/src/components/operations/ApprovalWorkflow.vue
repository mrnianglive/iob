<template>
  <div class="card mt-8">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Workflow d'approbation</h3>
    
    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
      <div class="flex">
        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">
            Cette opération nécessite une approbation
          </h3>
          <p class="mt-1 text-sm text-yellow-700">
            Montant: {{ formatCurrency(operation.amount) }} - Seuil: {{ formatCurrency(10000) }}
          </p>
        </div>
      </div>
    </div>

    <div class="space-y-4">
      <div v-for="approval in approvals" :key="approval.level" class="flex items-center justify-between p-4 border rounded-lg">
        <div class="flex items-center space-x-3">
          <div :class="getStatusColor(approval.status)" class="h-8 w-8 rounded-full flex items-center justify-center">
            <component :is="getStatusIcon(approval.status)" class="h-5 w-5 text-white" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">{{ approval.title }}</p>
            <p class="text-sm text-gray-500">{{ approval.approver }}</p>
          </div>
        </div>
        <div class="text-sm text-gray-500">
          {{ approval.date ? formatDate(approval.date) : 'En attente' }}
        </div>
      </div>
    </div>

    <div v-if="canApprove" class="mt-6 flex justify-end space-x-3">
      <button
        @click="reject"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
      >
        Rejeter
      </button>
      <button
        @click="approve"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
      >
        Approuver
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  ExclamationTriangleIcon,
  CheckCircleIcon, 
  ClockIcon, 
  XCircleIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  operation: Object
})

const emit = defineEmits(['approved', 'rejected'])

const approvals = ref([
  {
    level: 1,
    title: 'Approbation Superviseur',
    approver: 'Jean Dupont',
    status: 'pending',
    date: null
  },
  {
    level: 2,
    title: 'Approbation Manager',
    approver: 'Marie Martin',
    status: 'waiting',
    date: null
  }
])

const canApprove = computed(() => {
  return approvals.value.some(a => a.status === 'pending')
})

const getStatusColor = (status) => {
  const colors = {
    'approved': 'bg-green-500',
    'pending': 'bg-yellow-500',
    'rejected': 'bg-red-500',
    'waiting': 'bg-gray-400'
  }
  return colors[status] || 'bg-gray-400'
}

const getStatusIcon = (status) => {
  const icons = {
    'approved': CheckCircleIcon,
    'pending': ClockIcon,
    'rejected': XCircleIcon,
    'waiting': ClockIcon
  }
  return icons[status] || ClockIcon
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const approve = () => {
  const pendingApproval = approvals.value.find(a => a.status === 'pending')
  if (pendingApproval) {
    pendingApproval.status = 'approved'
    pendingApproval.date = new Date()
    
    const nextApproval = approvals.value.find(a => a.status === 'waiting')
    if (nextApproval) {
      nextApproval.status = 'pending'
    } else {
      emit('approved')
    }
  }
}

const reject = () => {
  const pendingApproval = approvals.value.find(a => a.status === 'pending')
  if (pendingApproval) {
    pendingApproval.status = 'rejected'
    pendingApproval.date = new Date()
    emit('rejected')
  }
}
</script>
