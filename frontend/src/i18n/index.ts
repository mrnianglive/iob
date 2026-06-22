import { createI18n } from 'vue-i18n';
import fr from './locales/fr';
import en from './locales/en';

// Get saved language or detect browser language
function getDefaultLanguage(): string {
  const savedLang = localStorage.getItem('language');
  if (savedLang && ['fr', 'en'].includes(savedLang)) {
    return savedLang;
  }
  
  // Detect browser language
  const browserLang = navigator.language.split('-')[0];
  return ['fr', 'en'].includes(browserLang) ? browserLang : 'fr';
}

const i18n = createI18n({
  legacy: false,
  locale: getDefaultLanguage(),
  fallbackLocale: 'fr',
  messages: {
    fr,
    en,
  },
  numberFormats: {
    fr: {
      currency: {
        style: 'currency',
        currency: 'XOF',
        notation: 'standard',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        useGrouping: false,
      },
    },
    en: {
      currency: {
        style: 'currency',
        currency: 'XOF',
        notation: 'standard',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        useGrouping: false,
      },
    },
  },
  datetimeFormats: {
    fr: {
      short: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      },
    },
    en: {
      short: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      },
    },
  },
});

export default i18n;