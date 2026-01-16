/**
 * Set cookie.
 *
 * @param {string} name   - Cookie name
 * @param {string} value  - Cookie value
 * @param {number} expiry - Cookie expiry time in seconds
 *
 * @return {void}
 */
export function setCookie(name, value, expiry) {
  let date = new Date();
  date.setTime(date.getTime() + expiry * 1000);

  document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

/**
 * Get cookie value.
 *
 * @param {string} name   - Cookie name
 *
 * @return {string} Cookie value
 */
export function getCookie(name) {
  const cookieValue = document.cookie
    .split('; ')
    .find((row) => row.startsWith(`${name}=`))
    ?.split('=')[1];

  return cookieValue;
}

/**
 * Delete cookie.
 *
 * @param {string} name - Cookie name
 *
 * @return {void}
 */
export function deleteCookie(name) {
  document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

/**
 * Check if cookie is set.
 *
 * @param {string} name - Cookie name
 *
 * @return {boolean}
 */
export function isCookieSet(name) {
  const dataCookie = document.cookie
    .split('; ')
    .some((value) => value.trim().startsWith(`${name}=`));

  return dataCookie;
}
