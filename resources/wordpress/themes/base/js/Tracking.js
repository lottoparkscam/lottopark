import Cookies from 'js-cookie';
import {
  getDomain,
  getPreparedApiUrl,
  isCasino,
  isLoggedAsWordpressAdmin,
} from '../../../../js/Helpers/UrlHelper';

(async () => {
  if (isLoggedAsWordpressAdmin()) {
    return;
  }

  const url = getPreparedApiUrl('tracking/run');
  const searchParams = new URLSearchParams(location.search);
  searchParams.forEach((value, key) => {
    url.searchParams.set(key, value);
  });

  const body = new FormData();
  const pathnameWithoutStartingSlash = location.pathname.substr(1);
  body.append('pathname', pathnameWithoutStartingSlash);

  if (window.shouldTriggerUserViewItemEvent) {
    body.append('shouldTriggerUserViewItem', true);
    body.append('lotterySlug', window.lotterySlug);
    body.append('pageName', window.pageName);
  }

  const response = await fetch(url, {
    credentials: 'include',
    method: 'POST',
    body,
  });

  const { redirect, scripts, gTag, fbPixel, affFbPixel, isCasinoCampaign } =
    await response.json();

  if (redirect) {
    window.location.href = redirect;
  }

  if (gTag && scripts) {
    const asyncGtagScript = document.createElement('script');
    asyncGtagScript.src = `https://www.googletagmanager.com/gtag/js?id=${gTag}`;
    asyncGtagScript.async = true;
    document.head.appendChild(asyncGtagScript);
  }

  if (scripts) {
    const gtagScript = document.createElement('script');
    gtagScript.text = scripts;
    document.head.appendChild(gtagScript);
  }

  if (fbPixel) {
    const pixelNoScript = document.createElement('noscript');
    const firstImage = document.createElement('img');
    firstImage.width = '1';
    firstImage.height = '1';
    firstImage.style.display = 'none';
    firstImage.src = `https://www.facebook.com/tr?id=${fbPixel}&ev=PageView&noscript=1`;
    pixelNoScript.appendChild(firstImage);
    document.head.appendChild(pixelNoScript);
  }

  if (affFbPixel) {
    const affPixelNoScript = document.createElement('noscript');
    const affImage = document.createElement('img');
    affImage.width = '1';
    affImage.height = '1';
    affImage.style.display = 'none';
    affImage.src = `https://www.facebook.com/tr?id=${affFbPixel}&ev=PageView&noscript=1`;
    affPixelNoScript.appendChild(affImage);
    document.head.appendChild(affPixelNoScript);
  }

  if (!Cookies.get('ref') && searchParams.get('ref'))
    Cookies.set('ref', searchParams.get('ref').toLowerCase(), {
      domain:
        isCasino() && isCasinoCampaign ? 'casino.' + getDomain() : getDomain(),
      expires: 31104000,
    });
})();
