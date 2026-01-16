import { setCookie, isCookieSet } from './Cookie';

export const initAppDownloadBanner = () => {
  const bannerContainer = document.getElementById('mobile-app-download-banner-container');
  if (!bannerContainer) {
    return;
  }

  if (isCookieSet('hideMobileAppDownloadBanner')) {
    bannerContainer.remove();
    return;
  }

  const YEAR_IN_SECONDS = 31536000;
  const APP_STORE_DOWNLOAD_TEXT = 'the App Store';
  const GOOGLE_PLAY_STORE_DOWNLOAD_TEXT = 'Google Play';
  const isMobile = window.innerWidth < 1250;
  const userAgent = navigator.userAgent || navigator.vendor || window.opera;
  const isAndroid = /Android/i.test(userAgent);
  const isMacOs = /iPhone|iPad|Mac OS/i.test(userAgent);
  const downloadButton = document.getElementById('download-button');
  const hideButton = document.getElementById('hide-button');
  const bannerDescription = document.getElementById('banner-description');

  if ((isAndroid || isMacOs) && isMobile) {
    if (isAndroid) {
      downloadButton.href = GOOGLE_PLAY_STORE_URL;
      if (bannerDescription) {
        bannerDescription.textContent = GOOGLE_PLAY_STORE_DOWNLOAD_TEXT;
      }
    } else if (isMacOs) {
        downloadButton.href = APP_STORE_URL;
      if (bannerDescription) {
        bannerDescription.textContent = APP_STORE_DOWNLOAD_TEXT;
      }
    }
  }

  if (hideButton) {
    hideButton.addEventListener('click', () => {
      bannerContainer.style.display = 'none';
      setCookie('hideMobileAppDownloadBanner', 'true', YEAR_IN_SECONDS);
    }, {once: true});
  }

  downloadButton.addEventListener('click', () => {
    bannerContainer.style.display = 'none';
  }, {once: true});
};
