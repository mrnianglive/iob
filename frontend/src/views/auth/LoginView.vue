<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 py-12 px-4 sm:px-6 lg:px-8">
    <!-- Language Selector -->
    <div class="absolute top-4 right-4">
      <LanguageSelector />
    </div>

    <div class="max-w-md w-full space-y-8">
      <!-- Logo and Title -->
      <div class="text-center">
        <div class="mx-auto h-16 w-16 bg-primary-600 rounded-full flex items-center justify-center">
          <BanknotesIcon class="h-10 w-10 text-white" />
        </div>
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          IOB Banking System
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ $t('auth.loginTitle') }}
        </p>
      </div>

      <!-- Login Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">{{ $t('auth.email') }}</label>
            <input
              id="email"
              v-model="credentials.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              :placeholder="$t('auth.email')"
              :disabled="loading"
            />
          </div>
          <div>
            <label for="password" class="sr-only">{{ $t('auth.password') }}</label>
            <input
              id="password"
              v-model="credentials.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              :placeholder="$t('auth.password')"
              :disabled="loading"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="rememberMe"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              {{ $t('auth.rememberMe') }}
            </label>
          </div>

          <div class="text-sm">
            <router-link
              to="/forgot-password"
              class="font-medium text-primary-600 hover:text-primary-500"
            >
              {{ $t('auth.forgotPassword') }}
            </router-link>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <LockClosedIcon class="h-5 w-5 text-primary-500 group-hover:text-primary-400" />
            </span>
            <span v-if="!loading">{{ $t('auth.login') }}</span>
            <span v-else>{{ $t('common.loading') }}</span>
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-md bg-danger-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-5 w-5 text-danger-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm text-danger-800">{{ error }}</p>
            </div>
          </div>
        </div>
      </form>

      <!-- Demo Credentials -->
      <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <p class="text-xs text-gray-600 text-center">
          <strong>Demo:</strong> admin@iob.com / password123
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import {
  BanknotesIcon,
  LockClosedIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/solid';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();

const credentials = ref({
  email: '',
  password: '',
});
const rememberMe = ref(false);
const loading = ref(false);
const error = ref('');

async function handleLogin() {
  loading.value = true;
  error.value = '';

  try {
    const result = await authStore.login(credentials.value);
    
    if (result.require2FA) {
      // Redirect to 2FA page
      await router.push('/2fa');
    } else {
      // Redirect to intended page or dashboard
      const redirect = route.query.redirect as string || '/dashboard';
      await router.push(redirect);
    }
  } catch (err: any) {
    error.value = err.message || t('auth.loginError');
  } finally {
    loading.value = false;
  }
}
</script>