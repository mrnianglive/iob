<template>
  <div class="data-table">
    <!-- Search and filters -->
    <div v-if="searchable || $slots.filters" class="mb-4 flex justify-between items-center">
      <div v-if="searchable" class="flex-1 max-w-md">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="input-field"
          @input="handleSearch"
        />
      </div>
      <div v-if="$slots.filters" class="ml-4">
        <slot name="filters"></slot>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                column.sortable ? 'cursor-pointer hover:bg-gray-100' : '',
                column.class
              ]"
              @click="column.sortable && handleSort(column.key)"
            >
              <div class="flex items-center">
                <span>{{ column.label }}</span>
                <ChevronUpDownIcon
                  v-if="column.sortable"
                  class="ml-2 h-4 w-4 text-gray-400"
                />
                <ChevronUpIcon
                  v-if="sortKey === column.key && sortOrder === 'asc'"
                  class="ml-2 h-4 w-4 text-gray-700"
                />
                <ChevronDownIcon
                  v-if="sortKey === column.key && sortOrder === 'desc'"
                  class="ml-2 h-4 w-4 text-gray-700"
                />
              </div>
            </th>
            <th v-if="actions" class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(item, index) in paginatedData"
            :key="item[rowKey] || index"
            :class="[
              'hover:bg-gray-50',
              selectable ? 'cursor-pointer' : ''
            ]"
            @click="selectable && handleRowClick(item)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="['px-6 py-4 whitespace-nowrap text-sm', column.class]"
            >
              <slot :name="`cell-${column.key}`" :item="item" :value="getNestedValue(item, column.key)">
                <component
                  v-if="column.component"
                  :is="column.component"
                  :value="getNestedValue(item, column.key)"
                  :item="item"
                />
                <span v-else>{{ formatValue(getNestedValue(item, column.key), column.format) }}</span>
              </slot>
            </td>
            <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :item="item">
                <div class="flex justify-end space-x-2">
                  <button
                    v-for="action in actions"
                    :key="action.key"
                    :class="[
                      'text-sm',
                      action.class || 'text-primary-600 hover:text-primary-900'
                    ]"
                    @click.stop="handleAction(action.key, item)"
                  >
                    <component v-if="action.icon" :is="action.icon" class="h-4 w-4" />
                    <span v-else>{{ action.label }}</span>
                  </button>
                </div>
              </slot>
            </td>
          </tr>
          <tr v-if="loading">
            <td :colspan="columns.length + (actions ? 1 : 0)" class="px-6 py-4 text-center">
              <div class="flex justify-center">
                <div class="spinner"></div>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && paginatedData.length === 0">
            <td :colspan="columns.length + (actions ? 1 : 0)" class="px-6 py-4 text-center text-gray-500">
              {{ emptyText }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && totalPages > 1" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Affichage de
        <span class="font-medium">{{ startItem }}</span>
        à
        <span class="font-medium">{{ endItem }}</span>
        sur
        <span class="font-medium">{{ totalItems }}</span>
        résultats
      </div>
      <div class="flex space-x-2">
        <button
          :disabled="currentPage === 1"
          class="btn-outline"
          @click="goToPage(currentPage - 1)"
        >
          <ChevronLeftIcon class="h-5 w-5" />
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          :class="[
            'px-3 py-1 rounded',
            page === currentPage
              ? 'bg-primary-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50 border'
          ]"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
        <button
          :disabled="currentPage === totalPages"
          class="btn-outline"
          @click="goToPage(currentPage + 1)"
        >
          <ChevronRightIcon class="h-5 w-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  ChevronUpDownIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  format?: 'date' | 'datetime' | 'currency' | 'number' | 'boolean';
  class?: string;
  component?: any;
}

interface Action {
  key: string;
  label?: string;
  icon?: any;
  class?: string;
}

interface Props {
  columns: Column[];
  data: any[];
  rowKey?: string;
  loading?: boolean;
  searchable?: boolean;
  searchPlaceholder?: string;
  paginated?: boolean;
  pageSize?: number;
  selectable?: boolean;
  actions?: Action[];
  emptyText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  rowKey: 'id',
  loading: false,
  searchable: false,
  searchPlaceholder: 'Rechercher...',
  paginated: true,
  pageSize: 10,
  selectable: false,
  emptyText: 'Aucune donnée disponible',
});

const emit = defineEmits<{
  'row-click': [item: any];
  'action': [key: string, item: any];
  'search': [query: string];
  'sort': [key: string, order: 'asc' | 'desc'];
}>();

// State
const searchQuery = ref('');
const sortKey = ref<string | null>(null);
const sortOrder = ref<'asc' | 'desc'>('asc');
const currentPage = ref(1);

// Computed
const filteredData = computed(() => {
  let result = [...props.data];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(item => {
      return props.columns.some(column => {
        const value = getNestedValue(item, column.key);
        return value && value.toString().toLowerCase().includes(query);
      });
    });
  }

  // Apply sorting
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = getNestedValue(a, sortKey.value!);
      const bVal = getNestedValue(b, sortKey.value!);
      
      if (aVal === bVal) return 0;
      
      const comparison = aVal < bVal ? -1 : 1;
      return sortOrder.value === 'asc' ? comparison : -comparison;
    });
  }

  return result;
});

const totalItems = computed(() => filteredData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / props.pageSize));

const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value;
  
  const start = (currentPage.value - 1) * props.pageSize;
  const end = start + props.pageSize;
  
  return filteredData.value.slice(start, end);
});

const startItem = computed(() => {
  if (totalItems.value === 0) return 0;
  return (currentPage.value - 1) * props.pageSize + 1;
});

const endItem = computed(() => {
  return Math.min(currentPage.value * props.pageSize, totalItems.value);
});

const visiblePages = computed(() => {
  const pages: number[] = [];
  const maxVisible = 5;
  const halfVisible = Math.floor(maxVisible / 2);
  
  let start = Math.max(1, currentPage.value - halfVisible);
  let end = Math.min(totalPages.value, currentPage.value + halfVisible);
  
  if (end - start + 1 < maxVisible) {
    if (start === 1) {
      end = Math.min(totalPages.value, maxVisible);
    } else {
      start = Math.max(1, totalPages.value - maxVisible + 1);
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
function getNestedValue(obj: any, path: string): any {
  return path.split('.').reduce((acc, part) => acc?.[part], obj);
}

function formatValue(value: any, format?: string): string {
  if (value === null || value === undefined) return '-';
  
  switch (format) {
    case 'date':
      return format(new Date(value), 'dd/MM/yyyy', { locale: fr });
    case 'datetime':
      return format(new Date(value), 'dd/MM/yyyy HH:mm', { locale: fr });
    case 'currency':
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
      }).format(value);
    case 'number':
      return new Intl.NumberFormat('fr-FR').format(value);
    case 'boolean':
      return value ? 'Oui' : 'Non';
    default:
      return value.toString();
  }
}

function handleSearch() {
  currentPage.value = 1;
  emit('search', searchQuery.value);
}

function handleSort(key: string) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
  
  emit('sort', key, sortOrder.value);
}

function handleRowClick(item: any) {
  emit('row-click', item);
}

function handleAction(key: string, item: any) {
  emit('action', key, item);
}

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

// Reset page when data changes
watch(() => props.data, () => {
  currentPage.value = 1;
});
</script>