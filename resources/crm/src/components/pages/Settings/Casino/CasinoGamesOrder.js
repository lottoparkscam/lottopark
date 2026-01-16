import React from 'react';
import CrmContext from '../../../../helpers/context';
import Loading from '../../../elements/loading';
import { withRouter } from 'react-router-dom';
import axios from 'axios';
import { Card, Image, Container, Button, Input } from 'semantic-ui-react';
import SearchInput from '../../../elements/SearchInput';
import { getWhitelabelUrl } from '../../../../../../js/Helpers/UrlHelper';

class CasinoGamesOrder extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      games: [],
      isLoading: true,
      searchResults: [],
      searchInput: '',
      newGamesOrder: [],
      gameOrder: [],
      category: 'homepage',
      whitelabelId: null,
    };
  }

  componentDidMount() {
    this.setState(
      {
        whitelabelId: this.context.choosedWhitelabel.id,
      },
      () => {
        this.getCurrentGamesOrder();
        this.getAllGamesForThisWhitelabel();
      },
    );
  }

  getAllGamesForThisWhitelabel() {
    this.setState({ isLoading: true });
    const { whitelabelId } = this.state;
    try {
      axios
        .get(
          `/Crm/Modules/Settings/Casino/GamesOrder/enabled_games_for_whitelabel?whitelabelId=${whitelabelId}`,
        )
        .then((res) => {
          if (res) {
            if (res.data.code == 200) {
              this.setState({
                games: Object.values(res.data.enabledSlotGames),
              });
              this.setState({ isLoading: false });
            }
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  getCurrentGamesOrder() {
    const { category, whitelabelId } = this.state;

    try {
      axios
        .get(
          `/Crm/Modules/Settings/Casino/GamesOrder/games_order?whitelabelId=${whitelabelId}&category=${category}`,
        )
        .then((res) => {
          if (res) {
            if (res.data.code == 200) {
              this.setState({
                newGamesOrder: Object.values(res.data.orderWithGameData),
              });
              this.setState({ isLoading: false });
            }
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  updateGamesOrder() {
    const { newGamesOrder, category, whitelabelId } = this.state;
    this.setState({ isLoading: true });
    try {
      axios
        .post('/Crm/Modules/Settings/Casino/GamesOrder/update_games_order', {
          newGamesOrder,
          category,
          whitelabelId,
        })
        .then((res) => {
          if (res) {
            this.setState({ isLoading: false });
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  searchItems(searchValue) {
    const { games, searchInput } = this.state;

    this.setState({ searchInput: searchValue });
    if (searchInput !== '') {
      const filteredData = games.filter((item) => {
        return Object.values(item.name)
          .join('')
          .toLowerCase()
          .includes(searchInput.toLowerCase());
      });
      this.setState({ searchResults: filteredData });
    }
  }

  handleSearch(searchValue) {
    const isValidSearchInput = searchValue != '' && searchValue.length >= 5;
    if (isValidSearchInput) {
      this.searchItems(searchValue);
    }
  }

  getOrderGamesIds(array, col) {
    let column = [];
    for (var i = 0; i < array.length; i++) {
      column.push(parseInt(array[i][col]['id']));
    }
    return column;
  }

  deleteGameFromApi = () => {
    this.setState({ isLoading: true });
    const { newGamesOrder, category, whitelabelId } = this.state;

    try {
      axios
        .post(
          '/Crm/Modules/Settings/Casino/GamesOrder/delete_game_from_order',
          { newGamesOrder, category, whitelabelId },
        )
        .then((res) => {
          this.setState({ isLoading: false });
        });
    } catch (e) {
      console.log('e ' + e);
    }
  };

  deleteGameFromOrder(data) {
    const index = this.state.newGamesOrder.indexOf(data);
    this.state.newGamesOrder.splice(index, 1);

    const filtered = this.state.newGamesOrder.filter(function (element, id) {
      element.gameOrder = id;
      return element != null;
    });

    this.setState({ newGamesOrder: filtered }, () => {
      this.deleteGameFromApi();
    });
  }

  changeGameOrderState(gameId, oldGameOrder, newGameOrder) {
    this.setState({ gameOrder: [gameId, oldGameOrder, newGameOrder] });
  }

  moveGame(buttonGameId) {
    const { newGamesOrder, gameOrder } = this.state;

    const gameId = gameOrder[0];
    const oldGameOrder = gameOrder[1];
    const newGameOrder = gameOrder[2];

    if ((gameId, oldGameOrder, newGameOrder && buttonGameId === gameId)) {
      this.setState({ isLoading: true });

      const updatedGamesOrder = this.move(
        newGamesOrder,
        oldGameOrder,
        newGameOrder,
      );
      this.setState({ newGamesOrder: updatedGamesOrder }, () => {
        this.updateGamesOrder();
        this.setState({ gameOrder: [], isLoading: false });
      });
    }
  }

  /** @param {array} arr */
  move(arr, oldIndex, newIndex) {
    if (newIndex < 0) {
      return arr;
    }

    let length = arr.length;
    if (newIndex >= length) {
      alert(`Last available order is ${--length}`);
      return arr;
    }

    const oldIndexInt = parseInt(oldIndex);
    const newIndexInt = parseInt(newIndex);

    const filtered = arr.filter(function (element, id) {
      if (id == oldIndexInt) {
        element.gameOrder = newIndexInt;
      }
      if (id == newIndexInt) {
        element.gameOrder = oldIndexInt;
      }
      return element != null;
    });

    return filtered;
  }

  cleanCache() {
    const { whitelabelId } = this.state;
    try {
      axios.get(
        `/Crm/Modules/Settings/Casino/GamesOrder/clean_cache?whitelabelId=${whitelabelId}`,
      );
    } catch (e) {
      console.log('e ' + e);
    }
  }

  render() {
    const { isLoading, newGamesOrder, games } = this.state;

    const handleResultSelect = (e, { result }) => {
      const gameWasAddedBefore = this.getOrderGamesIds(
        newGamesOrder,
        'game',
      ).includes(parseInt(result.id));

      if (gameWasAddedBefore) {
        alert('Provided game actually exists in ordered list.');
        return;
      }

      this.setState(
        (previousState) => ({
          newGamesOrder: [
            ...previousState.newGamesOrder,
            { game: result, gameOrder: parseInt(newGamesOrder.length) },
          ],
        }),
        () => {
          this.updateGamesOrder();
        },
      );
    };

    const resRender = ({ id, name, image, provider }) => (
      <span key={id}>
        <Image
          src={image.startsWith('/wp-content') ? whitelabelUrl + image : image}
        />
        {name} from {provider}
      </span>
    );
    const whitelabelUrl = getWhitelabelUrl();
    return isLoading ? (
      <Loading />
    ) : (
      <Container>
        <Container style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Container>
            <SearchInput
              SearchResult={'name'}
              dataToSearchIn={games}
              keysToSearchIn={['name', 'uuid']}
              notFoundMessage="No games found."
              onSearchChange={(e) => this.handleSearch(e.target.value)}
              searchResultsListView={resRender}
              handleSelect={handleResultSelect}
            />
          </Container>
          <Button
            style={{ width: 200 }}
            positive
            onClick={() => this.cleanCache()}
          >
            Clean Cache
          </Button>
        </Container>
        <Card.Group
          className="gamesOrderContainer"
          itemsPerRow={3}
          stackable
          doubling
          style={{ marginTop: 20 }}
        >
          {newGamesOrder
            .sort((a, b) => (a.gameOrder > b.gameOrder ? 1 : -1))
            .map((data, order) => {
              const image = data.game.image.startsWith('/wp-content')
                ? whitelabelUrl + data.game.image
                : data.game.image;
              const isDeleted = parseInt(data.game.is_deleted) === 1;
              const playSlug =
                parseInt(data.game.has_lobby) === 1
                  ? '/casino-lobby'
                  : '/casino-play';
              const gameUrl =
                'https://' +
                window.casinoPrefix +
                '.' +
                this.context.choosedWhitelabel.domain +
                playSlug +
                '/?game_uuid=' +
                data.game.uuid;
              return (
                <Card key={data.game.id}>
                  <Image
                    src={image}
                    title={
                      isDeleted
                        ? "This game is temporary disabled. It won't be displayed on casino page."
                        : ''
                    }
                    alt={data.game.name}
                    style={{ filter: isDeleted ? 'saturate(0.3)' : 'unset' }}
                    wrapped
                    ui={false}
                  />
                  <Card.Content>
                    <Card.Header>
                      {data.gameOrder} - {data.game.name}
                    </Card.Header>
                    <Card.Description>
                      Uuid: <span>{data.game.uuid}</span>
                      <br />
                      Url:{' '}
                      <a href={gameUrl} target="_blank">
                        Open this game
                      </a>
                    </Card.Description>
                    <Card.Meta>{data.game.provider}</Card.Meta>
                    <div
                      style={{
                        marginTop: 'auto',
                      }}
                    >
                      <Input
                        style={{ marginTop: 10, marginBottom: 10 }}
                        icon="move"
                        iconPosition="left"
                        placeholder="New order"
                        type="number"
                        fluid
                        onChange={(e) =>
                          this.changeGameOrderState(
                            data.game.id,
                            order,
                            e.target.value,
                          )
                        }
                      ></Input>
                      <Button.Group style={{ marginTop: 10, width: '100%' }}>
                        <Button
                          positive
                          game={data.game}
                          onClick={() => this.moveGame(data.game.id)}
                        >
                          Move
                        </Button>
                        <Button.Or />
                        <Button
                          negative
                          onClick={() => {
                            this.deleteGameFromOrder(data);
                          }}
                        >
                          Delete
                        </Button>
                      </Button.Group>
                    </div>
                  </Card.Content>
                </Card>
              );
            })}
        </Card.Group>
      </Container>
    );
  }
}

CasinoGamesOrder.contextType = CrmContext;
export default withRouter(CasinoGamesOrder);
