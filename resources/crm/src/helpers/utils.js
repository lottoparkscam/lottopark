export function filterDates(
  columns,
  filters,
  date,
  colName,
  additionalProperties = {},
) {
  var colIndex = columns.findIndex((x) => x.name == colName);
  let index = filters.findIndex((x) => x.column == colName);
  let end_date = '';
  let start_date = '';
  if (date) {
    let end = date[1];
    let start = date[0];
    start_date =
      start.getFullYear() +
      '-' +
      (parseInt(start.getMonth()) + 1) +
      '-' +
      start.getDate();
    end_date =
      end.getFullYear() +
      '-' +
      (parseInt(end.getMonth()) + 1) +
      '-' +
      end.getDate();
  }
  if (index == -1) {
    if (date) {
      columns[colIndex].filter = date;

      let filter = {
        column: colName,
        startDate: start_date,
        endDate: end_date,
        ...additionalProperties,
      };
      filters.push(filter);
    }
  } else {
    if (index > -1) {
      if (date) {
        columns[colIndex].filter = date;
        filters[index].startDate = start_date;
        filters[index].endDate = end_date;
      } else {
        filters.splice(index, 1);
        columns[colIndex].filter = '';
      }
    }
  }
  return [columns, filters];
}

export function filterAmounts(columns, filters, colName, value) {
  let name = colName.split('-');
  let column = name[0];
  let fromto = name[1];

  var colIndex = columns.findIndex((x) => x.name == column);
  let index = filters.findIndex((x) => x.column == column);
  let range = columns[colIndex].filter;
  if (fromto == 'from') range[0] = value;
  else if (fromto == 'to') range[1] = value;
  columns[colIndex].filter = range;
  let filter = { column: column, start: range[0], end: range[1] };
  if (index == -1) {
    filters.push(filter);
  } else {
    if (range[0] == 0 && range[1] == 0) filters.splice(index, 1);
    else filters[index] = filter;
  }
  return [columns, filters];
}

export function prepareFilters(filters, name, value) {
  let index = filters.findIndex((x) => {
    return x.column == name;
  });
  if (index !== -1) {
    if (value == '' || value == null) {
      filters.splice(index, 1);
    } else filters[index].value = value;
  } else {
    let filter = { column: name, value: value };
    filters.push(filter);
  }
  return filters;
}

export function getDaysArray(start, end) {
  let days = [];

  var day = 1000 * 60 * 60 * 24;

  var diff = (start.getTime() - end.getTime()) / day;
  for (var i = 0; i <= diff; i++) {
    var xx = end.getTime() + day * i;

    days.push(new Date(xx));
  }

  return days;
}

export function prepareFiltersFromQuery(columns, query_params) {
  let filters = [];
  Object.keys(query_params).forEach((filter) => {
    let index = columns.findIndex((x) => x.name == filter);
    let filter_obj = { column: filter, value: query_params[filter] };
    columns[index].filter = query_params[filter];
    columns[index].shown = true;
    filters.push(filter_obj);
  });
  return [filters, columns];
}
