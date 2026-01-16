import {render, waitFor, screen, fireEvent, act} from '@testing-library/react';
import {BrowserRouter} from 'react-router-dom';
import '@testing-library/jest-dom';
import CasinoGamesOrder from '../../../../../components/pages/Settings/Casino/CasinoGamesOrder';
import CrmContext from '../../../../../helpers/context';
import GamesOrder from '../../../../../__mocks__/data/Components/Pages/Settings/Casino/GamesOrder';

beforeEach(() => {
  render(
      <CrmContext.Provider value={{choosedWhitelabel: {id: 1}}}>
        <BrowserRouter>
          <CasinoGamesOrder/>
        </BrowserRouter>
      </CrmContext.Provider>
  );
});

describe('CasinoGamesOrder Component', () => {
  test('It should render first game from API', async () => {
    const firstGameOrderPrefix = '0 - ';
    const firstGameName = GamesOrder.orderWithGameData[0].game.name;
    const findFirstGameNameInRenderedComponent = await waitFor(() =>
        screen.findByText(new RegExp(firstGameOrderPrefix + firstGameName, 'i'))
    );

    expect(findFirstGameNameInRenderedComponent).toBeInTheDocument();
  });

  test('It should be able to change the order of the games', async () => {
    const orderInputElement = await waitFor(() =>
        screen.getAllByPlaceholderText('New order')[0] // first ui card
    );

    fireEvent.change(orderInputElement, {
      target: {value: '1'},
    });

    expect(orderInputElement.value).toBe('1');

    const firstGameMoveButton = screen.getAllByText('Move')[0];
    await act(async () => {
      fireEvent.click(firstGameMoveButton);
    });

    const firstGameOrderPrefix = '0 - ';
    const firstGameName = GamesOrder.orderWithGameData[1].game.name;
    const findFirstGameNameInRenderedComponent = await waitFor(() =>
        screen.findByText(new RegExp(firstGameOrderPrefix + firstGameName, 'i'))
    );

    expect(findFirstGameNameInRenderedComponent).toBeInTheDocument();
  });

  test('It should be able to delete the game', async () => {
    const deleteButtons = await waitFor(() =>
        screen.getAllByText('Delete')
    );

    // we only have 2 games so we should also have 2 delete buttons
    expect(deleteButtons).toHaveLength(2);

    // click the delete button on the first game
    fireEvent.click(deleteButtons[0]);

    const deleteButtonsNew = await waitFor(() =>
        screen.getAllByText('Delete')
    );

    // we deleted one game so we should only have 1 delete button
    expect(deleteButtonsNew).toHaveLength(1);

    const deletedGameName = GamesOrder.orderWithGameData[0].game.name;
    const isExistDeletedGameNameInDocument = screen.queryByText(
        new RegExp(deletedGameName, 'i')
    );

    expect(isExistDeletedGameNameInDocument).toBeNull();
  });

  test('It should be able to search the game', async () => {
    const searchInputElement = await waitFor(() =>
        screen.getByPlaceholderText('Search...')
    );

    const partOfTheGameName = GamesOrder.orderWithGameData[0].game.name.slice(0, 7);

    fireEvent.change(searchInputElement, {
      target: {value: partOfTheGameName},
    });

    const searchResultsElement = document.querySelector('.results');

    expect(searchInputElement.value).toBe(partOfTheGameName);

    await waitFor(() =>
        expect(searchResultsElement).toHaveTextContent(partOfTheGameName)
    );
  });
});
