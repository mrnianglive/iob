<template>
  <div class="language-selector">
    <Menu as="div" class="relative inline-block text-left">
      <div>
        <MenuButton
          class="inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <LanguageIcon class="h-5 w-5 mr-2 text-gray-400" />
          <span>{{ currentLanguageLabel }}</span>
          <ChevronDownIcon class="ml-2 -mr-1 h-5 w-5" aria-hidden="true" />
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
        <MenuItems
          class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        >
          <div class="py-1">
            <MenuItem
              v-for="lang in languages"
              :key="lang.code"
              v-slot="{ active }"
            >
              <button
                @click="changeLanguage(lang.code)"
                :class="[
                  active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                  currentLocale === lang.code ? 'bg-primary-50 text-primary-700' : '',
                  'group flex items-center px-4 py-2 text-sm w-full text-left'
                ]"
              >
                <span class="mr-3 text-lg">{{ lang.flag }}</span>
                <span>{{ lang.label }}</span>
                <CheckIcon
                  v-if="currentLocale === lang.code"
                  class="ml-auto h-5 w-5 text-primary-600"
                />
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { ChevronDownIcon, CheckIcon, LanguageIcon } from '@heroicons/vue/24/outline';

const { locale, t } = useI18n();

const languages = [
  { code: 'fr', label: 'Français', flag: '🇫🇷' },
  { code: 'en', label: 'English', flag: '🇬🇧' },
];

const currentLocale = computed(() => locale.value);

const currentLanguageLabel = computed(() => {
  const lang = languages.find(l => l.code === locale.value);
  return lang ? lang.label : 'Language';
});

function changeLanguage(langCode: string) {
  locale.value = langCode;
  localStorage.setItem('language', langCode);
  
  // Update HTML lang attribute
  document.documentElement.lang = langCode;
}
</script>