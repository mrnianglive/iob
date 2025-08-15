import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import { createI18n } from 'vue-i18n'
import App from './App.vue'
import './style.css'

// Import routes
import routes from './router/routes.js'

// Import i18n messages
import en from './locales/en.json'
import fr from './locales/fr.json'

// Create router
const router = createRouter({
  history: createWebHistory(),
  routes
})

// Create i18n
const i18n = createI18n({
  locale: 'fr',
  fallbackLocale: 'en',
  messages: {
    en,
    fr
  }
})

// Create app
const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

app.mount('#app')
