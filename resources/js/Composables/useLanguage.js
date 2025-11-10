import { ref, computed } from 'vue';

// Available languages with their metadata
const availableLanguages = [
  {
    code: 'en',
    name: 'English',
    native: 'English',
    flag: '/assets/flags/us.png',
  },
  {
    code: 'bn',
    name: 'Bengali',
    native: 'বাংলা',
    flag: '/assets/flags/bd.png',
  },
  {
    code: 'es',
    name: 'Spanish',
    native: 'Español',
    flag: '/assets/flags/es.png',
  },
  {
    code: 'fr',
    name: 'French',
    native: 'Français',
    flag: '/assets/flags/fr.png',
  },
  {
    code: 'de',
    name: 'German',
    native: 'Deutsch',
    flag: '/assets/flags/de.png',
  },
  {
    code: 'it',
    name: 'Italian',
    native: 'Italiano',
    flag: '/assets/flags/it.png',
  },
  {
    code: 'pt',
    name: 'Portuguese',
    native: 'Português',
    flag: '/assets/flags/pt.png',
  },
  {
    code: 'ru',
    name: 'Russian',
    native: 'Русский',
    flag: '/assets/flags/ru.png',
  },
  {
    code: 'zh',
    name: 'Chinese',
    native: '中文',
    flag: '/assets/flags/cn.png',
  },
  {
    code: 'ar',
    name: 'Arabic',
    native: 'العربية',
    flag: '/assets/flags/sa.png',
  },
  {
    code: 'hi',
    name: 'Hindi',
    native: 'हिन्दी',
    flag: '/assets/flags/in.png',
  },
];

// Current language state
const currentLanguageCode = ref('en');

// Initialize from localStorage or default
const initializeLanguage = () => {
  const saved = localStorage.getItem('app_language');
  if (saved && availableLanguages.find(lang => lang.code === saved)) {
    currentLanguageCode.value = saved;
  } else {
    // Try to detect from browser
    const browserLang = navigator.language.split('-')[0];
    const matchedLang = availableLanguages.find(lang => lang.code === browserLang);
    if (matchedLang) {
      currentLanguageCode.value = matchedLang.code;
    }
  }
  // Set HTML lang attribute
  document.documentElement.setAttribute('lang', currentLanguageCode.value);
};

// Initialize on module load
initializeLanguage();

export const useLanguage = () => {
  // Get current language object
  const currentLanguage = computed(() => {
    return availableLanguages.find(lang => lang.code === currentLanguageCode.value) || availableLanguages[0];
  });

  // Set language
  const setLanguage = (code) => {
    const language = availableLanguages.find(lang => lang.code === code);
    if (language) {
      currentLanguageCode.value = code;
      localStorage.setItem('app_language', code);
      document.documentElement.setAttribute('lang', code);
      
      // Emit event for other components to react
      window.dispatchEvent(new CustomEvent('language-changed', { detail: { code } }));
      
      // Reload page to apply language changes (optional - can be removed if using client-side translation)
      // router.reload({ only: ['translations'] });
    }
  };

  // Get all available languages
  const languages = computed(() => availableLanguages);

  // Get language by code
  const getLanguage = (code) => {
    return availableLanguages.find(lang => lang.code === code) || availableLanguages[0];
  };

  return {
    currentLanguage,
    currentLanguageCode: computed(() => currentLanguageCode.value),
    languages,
    setLanguage,
    getLanguage,
    initializeLanguage,
  };
};

