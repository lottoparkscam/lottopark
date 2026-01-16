import _ from 'lodash';
import React, { Component } from 'react';
import { Search, Grid } from 'semantic-ui-react';

const styleLink = document.createElement('link');
styleLink.rel = 'stylesheet';
styleLink.href =
  'https://cdn.jsdelivr.net/npm/semantic-ui/dist/semantic.min.css';
document.head.appendChild(styleLink);

const initialState = { isLoading: false, results: [], value: '' };

export default class SearchInput extends Component {
  constructor(props) {
    super(props);
  }

  state = initialState;

  handleSearchChange = (e, { value }) => {
    const { dataToSearchIn, keysToSearchIn } = this.props;
    this.setState({ isLoading: true, value });
    setTimeout(() => {
      const searchInput = this.state.value;
      const searchInputLength = searchInput.length;
      if (searchInputLength < 1) return this.setState(initialState);
      if (searchInputLength >= 5) {
        const regexp = new RegExp(_.escapeRegExp(searchInput), 'i'); // used inside eval
        const isMatch = (result) => {
          if (!result.title) {
            result.title = '';
          }

          let regexpCondition = '';
          const arrayLastKey = keysToSearchIn.length - 1;
          Array.from(keysToSearchIn).forEach((value, key) => {
            regexpCondition += 'regexp.test(result.' + value + ')';
            if (key != arrayLastKey) {
              regexpCondition += ' | ';
            }
          });

          return eval(regexpCondition);
        };
        this.setState({
          isLoading: false,
          results: _.filter(dataToSearchIn, isMatch),
        });
      }
    }, 300);
  };

  render() {
    const { isLoading, value, results } = this.state;
    const { notFoundMessage, searchResultsListView, handleSelect } = this.props;
    return (
      <Grid>
        <Grid.Column width={6}>
          <Search
            fluid
            loading={isLoading}
            onResultSelect={handleSelect}
            onSearchChange={_.debounce(this.handleSearchChange, 500, {
              leading: true,
            })}
            results={results}
            value={value}
            noResultsMessage={notFoundMessage}
            placeholder="Search..."
            minCharacters={5}
            icon="search"
            resultRenderer={searchResultsListView}
          />
        </Grid.Column>
      </Grid>
    );
  }
}
