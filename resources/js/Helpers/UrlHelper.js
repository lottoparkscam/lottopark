import { getCurrentLanguageWithLocale } from './LanguageHelper';

export function decorateUrlForCasino(url) {
  url.searchParams.set('is_casino', true);
}

export function decorateUrlWithLanguage(url) {
  url.searchParams.set('language', getCurrentLanguageWithLocale());
}

export function isCasino() {
  const hostname = getLocation().hostname;
  return (
    getCasinoPrefixes().filter((prefix) => hostname.startsWith(`${prefix}.`))
      .length > 0
  );
}

export function isNotCasino() {
  return !isCasino();
}

export function getDomain() {
  const casinoPrefixesWithDots = getCasinoPrefixes().map(
    (prefix) => `${prefix}.`,
  );
  const casinoPrefixesAsRegex = casinoPrefixesWithDots.join('|');
  const regex = new RegExp(`^(www.|api.|${casinoPrefixesAsRegex})`);
  return getLocation().hostname.replace(regex, '');
}

export function getWhitelabelUrl() {
  return 'https://' + getLocation().hostname.replace(/^(www.|admin.)/, '');
}

export function getWhitelabelCasinoUrl() {
  return (
    'https://' +
    window.casinoPrefix +
    '.' +
    getLocation().hostname.replace(/^(www.|admin.)/, '')
  );
}

export function getPreparedApiUrl(path) {
  const domain = getDomain();
  const url = new URL(`https://api.${domain}/api/internal/${path}`);

  if (isCasino()) {
    decorateUrlForCasino(url);
  }

  decorateUrlWithLanguage(url);

  return url;
}

export function isLoggedAsWordpressAdmin() {
  if (isInIframe()) {
    return false;
  }

  return (
    document.body.classList.contains('logged-in') ||
    window.parent.document.body.classList.contains('logged-in')
  );
}

export function getLocation() {
  let isEditWidgetPath = false;

  if (!isInIframe()) {
    isEditWidgetPath =
      window.parent.location.pathname === '/wp-admin/widgets.php';
  }

  const isWordpressWidgetsPreview =
    isLoggedAsWordpressAdmin() &&
    isEditWidgetPath &&
    window.location.hostname === '';
  if (isWordpressWidgetsPreview) {
    return window.parent.location;
  }

  return window.location;
}

export function getCurrentUrl() {
  return new URL(getLocation());
}

export function isNotWordpressAdminPage() {
  return !getLocation().pathname.includes('wp-admin');
}

function getCasinoPrefixes() {
  return window.casinoPrefixes ?? ['casino'];
}

export function isInIframe() {
  return window !== window.parent;
}
