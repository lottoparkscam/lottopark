import React from 'react';
import { CrmConsumer } from '../../helpers/context';

const Pagination = ({ page, itemsPerPage, totalItems, paginate }) => {
  const pageNums = [];

  for (let i = 1; i <= Math.ceil(totalItems / itemsPerPage); i++) {
    pageNums.push(i);
  }

  return (
    <CrmConsumer>
      {(modContext) =>
        modContext &&
        pageNums.length > 1 && (
          <ul className="pagination">
            <li
              className={
                'paginate_button page-item previous ' +
                (page == 1 && 'disabled')
              }
            >
              {' '}
              <a
                className="page-link cursor-pointer"
                onClick={() => paginate(page - 1)}
              >
                {modContext.textdomain.gettext('Previous')}
              </a>
            </li>
            {pageNums.map(
              (num) =>
                ((num >= page - 2 && num <= page + 2) || num == page) && (
                  <li
                    className={
                      'paginate_button page-item ' + (page == num && 'active')
                    }
                    key={num}
                  >
                    <a
                      className="page-link cursor-pointer"
                      onClick={() => paginate(num)}
                    >
                      {num}
                    </a>
                  </li>
                ),
            )}
            <li
              className={
                'paginate_button page-item next ' +
                (page == pageNums[pageNums.length - 1] && 'disabled')
              }
            >
              {' '}
              <a
                className="page-link cursor-pointer"
                onClick={() => paginate(page + 1)}
              >
                {modContext.textdomain.gettext('Next')}
              </a>
            </li>
          </ul>
        )
      }
    </CrmConsumer>
  );
};

export default Pagination;
