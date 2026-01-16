export function getCurrentLanguageWithLocale() {
  // This is global variable
  return typeof window.currentLanguageWithLocale !== 'undefined'
    ? window.currentLanguageWithLocale
    : 'en_GB.utf8';
}

export function isRightToLeftLanguage() {
  /* global IS_RIGHT_TO_LEFT_LANGUAGE */
  return Boolean(IS_RIGHT_TO_LEFT_LANGUAGE);
}
