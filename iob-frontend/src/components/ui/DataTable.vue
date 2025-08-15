<template>
  <div class="overflow-hidden">
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>
    
    <div v-else-if="data.length === 0" class="text-center py-8 text-gray-500">
      Aucune donnée disponible
    </div>
    
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="column.sortable && sort(column.key)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <ChevronUpDownIcon v-if="column.sortable" class="h-4 w-4" />
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="row in sortedData"
            :key="row.id"
            class="hover:bg-gray-50 cursor-pointer"
            @click="$emit('row-click', row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
            >
              <component
                :is="getCellComponent(column.format)"
                :value="row[column.key]"
                :format="column.format"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ChevronUpDownIcon } from '@heroicons/vue/24/outline'
import TableCell from './TableCell.vue'

const props = defineProps({
  columns: Array,
  data: Array,
  loading: Boolean
})

const emit = defineEmits(['row-click'])

const sortKey = ref('')
const sortOrder = ref('asc')

const sortedData = computed(() => {
  if (!sortKey.value) return props.data
  
  return [...props.data].sort((a, b) => {
    const aVal = a[sortKey.value]
    const bVal = b[sortKey.value]
    
    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })
})

const sort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const getCellComponent = (format) => {
  return TableCell
}
</script>
