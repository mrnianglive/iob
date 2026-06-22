<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Left side -->
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/dashboard" class="flex items-center">
                <BanknotesIcon class="h-8 w-8 text-primary-600" />
                <span class="ml-2 text-xl font-bold text-gray-900">IOB</span>
              </router-link>
            </div>

            <!-- Main Navigation -->
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                v-for="item in navigation"
                :key="item.name"
                :to="item.to"
                class="nav-link"
                :class="{
                  'nav-link-active': $route.path.startsWith(item.to),
                }"
              >
                {{ $t(item.label) }}
              </router-link>
            </div>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Language Selector -->
            <LanguageSelector />

            <!-- Notifications -->
            <button
              type="button"
              class="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100"
            >
              <BellIcon class="h-6 w-6" />
              <span class="sr-only">{{ $t('nav.notifications') }}</span>
            </button>

            <!-- User Menu -->
            <Menu as="div" class="relative">
              <div>
                <MenuButton class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                  <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                    <span class="text-white font-medium">
                      {{ userInitials }}
                    </span>
                  </div>
                  <ChevronDownIcon class="ml-2 h-5 w-5 text-gray-400" />
                </MenuButton>
              </div>

              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <MenuItem v-slot="{ active }">
                    <router-link
                      to="/profile"
                      :class="[
                        active ? 'bg-gray-100' : '',
                        'block px-4 py-2 text-sm text-gray-700'
                      ]"
                    >
                      <UserIcon class="inline-block h-4 w-4 mr-2" />
                      {{ $t('nav.profile') }}
                    </router-link>
                  </MenuItem>
                  
                  <MenuItem v-slot="{ active }">
                    <router-link
                      to="/settings"
                      :class="[
                        active ? 'bg-gray-100' : '',
                        'block px-4 py-2 text-sm text-gray-700'
                      ]"
                    >
                      <CogIcon class="inline-block h-4 w-4 mr-2" />
                      {{ $t('nav.settings') }}
                    </router-link>
                  </MenuItem>

                  <hr class="my-1" />

                  <MenuItem v-slot="{ active }">
                    <button
                      @click="handleLogout"
                      :class="[
                        active ? 'bg-gray-100' : '',
                        'block w-full text-left px-4 py-2 text-sm text-gray-700'
                      ]"
                    >
                      <ArrowLeftOnRectangleIcon class="inline-block h-4 w-4 mr-2" />
                      {{ $t('auth.logout') }}
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>

            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              type="button"
              class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
            >
              <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" />
              <XMarkIcon v-else class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-show="mobileMenuOpen" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.to"
            class="mobile-nav-link"
            :class="{
              'mobile-nav-link-active': $route.path.startsWith(item.to),
            }"
            @click="mobileMenuOpen = false"
          >
            {{ $t(item.label) }}
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-1">
      <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
          <router-view />
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500">
          © {{ currentYear }} IOB Banking System. All rights reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import {
  BanknotesIcon,
  BellIcon,
  UserIcon,
  CogIcon,
  ArrowLeftOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';

const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

const mobileMenuOpen = ref(false);
const currentYear = new Date().getFullYear();

const navigation = [
  { name: 'dashboard', to: '/dashboard', label: 'nav.dashboard' },
  { name: 'operations', to: '/operations', label: 'nav.operations' },
  { name: 'journal', to: '/journal', label: 'nav.journal' },
  { name: 'cashRegisters', to: '/cash-registers', label: 'nav.cashRegisters' },
  { name: 'analytics', to: '/analytics', label: 'nav.analytics' },
  { name: 'remittances', to: '/remittances', label: 'nav.remittances' },
];

// Add admin navigation if user is admin
if (authStore.isAdmin) {
  navigation.push({ name: 'admin', to: '/admin', label: 'nav.administration' });
}

const userInitials = computed(() => {
  if (authStore.user?.Name) {
    return authStore.user.Name.split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }
  return 'U';
});

async function handleLogout() {
  await authStore.logout();
  await router.push('/login');
}
</script>

<style scoped>
.nav-link {
  @apply inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors;
}

.nav-link-active {
  @apply border-primary-500 text-gray-900;
}

.mobile-nav-link {
  @apply block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300;
}

.mobile-nav-link-active {
  @apply bg-primary-50 border-primary-500 text-primary-700;
}
</style>