import {
  getCurrentLanguageWithLocale, isRightToLeftLanguage
} from '../../../../../../resources/js/Helpers/LanguageHelper';

let windowSpy;

beforeEach(() => {
  windowSpy = jest.spyOn(window, 'window', 'get');
});

afterEach(() => {
  windowSpy.mockRestore();
});

test('getCurrentLanguageWithLocale should return default language if locale is not provided', () => {
  expect(getCurrentLanguageWithLocale()).toEqual('en_GB.utf8');
});

test('getCurrentLanguageWithLocale should return default language if locale is provided', () => {
  windowSpy.mockImplementation(() => ({
    currentLanguageWithLocale: 'pl_PL.utf8'
  }));

  expect(getCurrentLanguageWithLocale()).toEqual('pl_PL.utf8');
});

test('isRightToLeftLanguage should return true when is RTL', () => {
  window.IS_RIGHT_TO_LEFT_LANGUAGE = 1;
  expect(isRightToLeftLanguage()).toBe(true);
});

test('isRightToLeftLanguage should return false when is not RTL', () => {
  window.IS_RIGHT_TO_LEFT_LANGUAGE = 0;
  expect(isRightToLeftLanguage()).toBe(false);
});
