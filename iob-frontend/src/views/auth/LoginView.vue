<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          {{ $t('auth.login') }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          IOB Partner Interface
        </p>
      </div>
      
      <!-- Two Factor Modal -->
      <TwoFactorModal 
        v-if="showTwoFactor" 
        @verify="handleTwoFactorVerify"
        @close="showTwoFactor = false"
        :loading="authStore.isLoading"
      />
      
      <!-- Login Form -->
      <LoginForm 
        v-else
        @submit="handleLogin" 
        :loading="authStore.isLoading"
        :error="authStore.error"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import LoginForm from '../../components/auth/LoginForm.vue'
import TwoFactorModal from '../../components/auth/TwoFactorModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const showTwoFactor = ref(false)

const handleLogin = async (credentials) => {
  try {
    const response = await authStore.login(credentials)
    
    if (response.requiresTwoFactor) {
      showTwoFactor.value = true
    } else {
      router.push('/dashboard')
    }
  } catch (error) {
    // Error is handled in the store
  }
}

const handleTwoFactorVerify = async (code) => {
  try {
    await authStore.verifyTwoFactor(code)
    router.push('/dashboard')
  } catch (error) {
    // Error is handled in the store
  }
}
</script>
