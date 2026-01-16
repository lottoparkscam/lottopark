import {render, fireEvent, screen, waitFor} from '@testing-library/react';
import {act} from "react";
import {BrowserRouter} from 'react-router-dom';
import '@testing-library/jest-dom';
import Generator from '../../../../components/pages/SeoWidgets/Generator';
import CrmContext from '../../../../helpers/context';
import axios from 'axios';

jest.mock('axios');
beforeEach(async () => {
    axios.get
        .mockResolvedValueOnce({ data: {
            '0': {
                name: 'Powerball',
                slug: 'powerball'
            },
            '1': {
                name: 'MegaMillions',
                slug: 'mega-millions'
            }
        }})
        .mockResolvedValueOnce({ data: [
                {code: 'aa_AA', full_code: 'aa_AA.utf8'},
                {code: 'zz_AA', full_code: 'zz_AA.utf8'},
            ]})
        .mockResolvedValueOnce({ data: [
                {code: 'EUR'},
                {code: 'AYX'},
            ]})

    await act( async () => render(
        <CrmContext.Provider value={{
            choosedWhitelabel: {id: 1},
            verifyToken: () => {},
        }}>
            <BrowserRouter>
                <Generator />
            </BrowserRouter>
        </CrmContext.Provider>
    ));
});

describe('Generator Component', () => {
  test('Correct iframe', async () => {
    // Given
    const iframe = screen.getByText('Iframe:');
    expect(iframe.innerHTML).toContain('?lotterySlug=powerball&amp;widgetType=pickNumbers\" width=\"100%\"' +
        ' height=\"700\"' +
        ' style=\"border:none;max-width:400px\"');

    const shortCode = screen.getByText('Wordpress ShortCode:');
    expect(shortCode.innerHTML).toContain('[seoWidget lottery_slug=\"powerball\" widget_type=\"pickNumbers\"' +
        ' width=\"400\" height=\"700\"]');

      // When
      const lotterySelect = screen.getByLabelText('Lottery:');
      fireEvent.change(lotterySelect, {target: {value: 'mega-millions'}});

      const widthInput = screen.getByLabelText('Width (px):');
      fireEvent.change(widthInput, {target: {value: '251'}});
      const heightInput = screen.getByLabelText('Height (px):');
      fireEvent.change(heightInput, {target: {value: '523'}});

      const languageSelect = screen.getByLabelText('Language:');
      fireEvent.change(languageSelect, {target: {value: 'zz_AA.utf8'}});

      const orderUrlInput = screen.getByLabelText('Order Url (with trailing slash at the end):');
      fireEvent.change(orderUrlInput, {target: {value: 'https://this.is/new/order/'}});

      const currencySelect = screen.getByLabelText('Currency Code:');
      fireEvent.change(currencySelect, {target: {value: 'AYX'}});

      // Then
      await waitFor(async () => {
          expect(iframe.innerHTML).toContain('?lotterySlug=mega-millions&amp;widgetType=pickNumbers' +
              '&amp;language=zz_AA.utf8&amp;orderUrl=https%3A%2F%2Fthis.is%2Fnew%2Forder%2F&amp;currencyCode=AYX"' +
              ' width=\"100%\"' +
              ' height=\"523\"' +
              ' style=\"border:none;max-width:251px\"');
          expect(shortCode.innerHTML).toContain('[seoWidget lottery_slug=\"mega-millions\" widget_type=\"pickNumbers\"' +
              ' width=\"251\" height=\"523\"]');
      })
  });
});
