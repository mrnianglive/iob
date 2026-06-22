<template>
  <router-view />
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useSocketStore } from '@/stores/socket';

const authStore = useAuthStore();
const socketStore = useSocketStore();

onMounted(async () => {
  // Check if user is already authenticated
  await authStore.checkAuth();
  
  // Connect to WebSocket if authenticated
  if (authStore.isAuthenticated) {
    socketStore.connect();
  }
});
</script>

<style>
#app {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>