import {rest} from 'msw';
import fooItemsData from '../__mocks__/data/fooItems';
import GamesOrder from '../__mocks__/data/Components/Pages/Settings/Casino/GamesOrder';
import EnabledGamesForWhitelabel from '../__mocks__/data/Components/Pages/Settings/Casino/EnabledGamesForWhitelabel';

export const handlers = [
  rest.get('https://api.example.test/foo', (req, res, ctx) => {
    return res(
        ctx.status(200),
        ctx.json(fooItemsData)
    );
  }),

  // CasinoGamesOrder.js
  rest.get('http://localhost/Crm/Modules/Settings/Casino/GamesOrder/games_order', (req, res, ctx) => {
    return res(
        ctx.status(200),
        ctx.json(GamesOrder)
    );
  }),

  rest.get('http://localhost/Crm/Modules/Settings/Casino/GamesOrder/enabled_games_for_whitelabel', (req, res, ctx) => {
    return res(
        ctx.status(200),
        ctx.json(EnabledGamesForWhitelabel)
    );
  }),

  rest.post('http://localhost/Crm/Modules/Settings/Casino/GamesOrder/update_games_order', (req, res, ctx) => {
    return res(
        ctx.status(200),
        ctx.json({'code': 200})
    );
  }),

  rest.post('http://localhost/Crm/Modules/Settings/Casino/GamesOrder/delete_game_from_order', (req, res, ctx) => {
    return res(
        ctx.status(200),
        ctx.json({'code': 200})
    );
  }),
];
