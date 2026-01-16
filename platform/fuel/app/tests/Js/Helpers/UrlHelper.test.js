import * as LanguageHelper from '../../../../../../resources/js/Helpers/LanguageHelper';
import * as UrlHelper from '../../../../../../resources/js/Helpers/UrlHelper';

let windowSpy;

beforeEach(() => {
  windowSpy = jest.spyOn(window, 'window', 'get');
});

test('decorateUrlForCasino should add is_casino param to provided url object', () => {
  const url = new URL('https://lottopark.work/casino');
  UrlHelper.decorateUrlForCasino(url);

  expect(url.searchParams.get('is_casino')).toBe('true')
});

test('decorateUrlWithLanguage should add language param to provided url object', () => {
  const url = new URL('https://lottopark.work/example');

  jest.spyOn(LanguageHelper, 'getCurrentLanguageWithLocale')
      .mockReturnValue('en_GB.utf8');

  UrlHelper.decorateUrlWithLanguage(url);

  expect(url.searchParams.get('language')).toBe('en_GB.utf8');
});
