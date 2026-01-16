window.onload = function () {
  refreshAmount();
  const container = document.querySelector('#invoice');
  container.addEventListener('click', removeRow);

  const addRowButton = document.querySelector('#add-row');
  addRowButton.addEventListener('click', addRow);

  getPrintButton().addEventListener('click', printInvoice);
  getEditButton().addEventListener('click', editInvoice);
};

function refreshAmount() {
  const currentAmount = document.querySelector('#amount');
  currentAmount.innerHTML = countAmount();
}

function addMarginToCurrentEditedRows() {
  const rows = getRows();
  rows.forEach((row) => {
    const firstChildrenItem = row.children.item(0);
    firstChildrenItem.style.marginLeft = '13.2px';
  });
}

function deleteEditedRowsMargin() {
  const rows = getRows();
  rows.forEach((row) => {
    const firstChildrenItem = row.children.item(0);
    firstChildrenItem.style.marginLeft = '0';
  });
}

function hideRemoveButtons() {
  const deleteButtons = document.querySelectorAll('.delete-row.closebtn');
  deleteButtons.forEach((closeButton) => {
    closeButton.style.display = 'none';
  });
}

function showRemoveButtons() {
  const deleteButtons = document.querySelectorAll('.delete-row.closebtn');
  deleteButtons.forEach((closeButton) => {
    closeButton.style.display = 'block';
  });
}

function enableInputs() {
  const inputs = document.querySelectorAll('input.grey-column-invoice');
  inputs.forEach((input) => {
    input.disabled = false;
  });
}

function disableInputs() {
  const inputs = document.querySelectorAll('input.grey-column-invoice');
  inputs.forEach((input) => {
    input.disabled = true;
  });
}

function changeEditButtonToSaveButton() {
  changeButtonEdit('orange', 'Save', 'on');
}

function changeSaveButtonToEditButton() {
  changeButtonEdit('white', 'Edit', 'off');
}

function changeButtonEdit(color, text, value) {
  const editButton = getEditButton();
  editButton.style.color = color;
  editButton.innerText = text;
  editButton.value = value;
}

function createNewRow(service, summary, amount) {
  const mainSection = document.querySelector('#main-section-invoice');
  const rows = getRows();
  let isNotDuplicate = false;
  rows.forEach((row) => {
    const serviceExisted = row.children.item(0).value.toLowerCase();
    if (service !== '' && serviceExisted === service.toLowerCase()) {
      row.children.item(2).value = (
        parseFloat(row.children.item(2).value) + parseFloat(amount)
      ).toFixed(2);
      updateInputsValue(row);
      refreshAmount();
      isNotDuplicate = true;
    }
  });
  if (isNotDuplicate) {
    return;
  }

  mainSection.innerHTML += generateRow(service, summary, amount);
}

function generateRow(services, summary, amount) {
  return `<div class="row-invoice">
                <input class="grey-column-invoice service custom-input" value="${services}" disabled>
                <input class="grey-column-invoice summary custom-input" value="${summary}" disabled>
                <input class="grey-column-invoice total custom-input" value="${amount}" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
                </div>`;
}

function generateEmptyInputAlert() {
  return `<div class="alert">
                <div> Field cannot be empty</div>
                <div class="closebtn">&times;</div>
                </div>`;
}

function addEmptyInputAlert() {
  const formAlert = document.querySelector('#new-row-alert');
  formAlert.innerHTML += generateEmptyInputAlert();
}

function takeValueForRow() {
  const service = document.querySelector('input#services').value;
  const summary = document.querySelector('input#summary').value;
  const amount = document.querySelector('input#total').value;
  const isNotEmptyAmount = amount !== '';
  if (summary && isNotEmptyAmount && service) {
    return {
      service: service,
      summary: summary,
      amount: parseFloat(amount).toFixed(2),
    };
  }
  return null;
}

function addRow() {
  const row = takeValueForRow();

  const isRowEmpty = !row;
  if (isRowEmpty) {
    addEmptyInputAlert();
    return;
  }

  const editButtonNotExits = getEditButton().style.display === 'none';
  if (editButtonNotExits) {
    showEditButton();
  }

  createNewRow(row.service, row.summary, row.amount);
  resetNewRowForm();
  refreshAmount();
}

function resetNewRowForm() {
  const newRowForm = document.querySelector('#new-row form');
  newRowForm.reset();
}

function editInvoice() {
  const isEdit = getEditButton().value === 'off';
  if (isEdit) {
    hidePrintButton();
    hideNewRowForm();
    changeEditButtonToSaveButton();
    addMarginToCurrentEditedRows();
    showRemoveButtons();
    enableInputs();
    return;
  }

  const rows = getRows();
  const rowsForEditNotExists = !rows.length;
  if (rowsForEditNotExists) {
    hideEditButton();
  }

  saveInputsValue();
  refreshAmount();
  showPrintButton();
  showNewRowForm();
  changeSaveButtonToEditButton();
  deleteEditedRowsMargin();
  hideRemoveButtons();
  disableInputs();
}

function saveInputsValue() {
  const rows = getRows();
  rows.forEach((row) => {
    updateInputsValue(row);
  });
}

function countAmount() {
  const rows = getRows();
  let incomeExist = false;
  let incomeAmount = 0.0;
  if (rows.length === 0) {
    return '0,00';
  }

  let amount = 0.0;
  rows.forEach((row) => {
    amount += parseFloat(row.children.item(2).value);
    const service = row.children.item(0).value.toLowerCase();
    if (window.isV1 === 1 && service === 'income') {
      incomeAmount = parseFloat(row.children.item(2).value);
      incomeExist = true;
    }
  });

  if (incomeExist) {
    return (incomeAmount - (amount - incomeAmount))
      .toFixed(2)
      .replace('.', ',');
  }

  return parseFloat(amount).toFixed(2).replace('.', ',');
}

function updateInputsValue(row) {
  const service = row.children.item(0);
  const summary = row.children.item(2);
  const amount = row.children.item(2);
  service.setAttribute('value', String(service.value));
  summary.setAttribute('value', String(summary.value));
  amount.value = parseFloat(amount.value).toFixed(2);
  amount.setAttribute('value', String(amount.value));
}

function removeRow() {
  const pressedElement = event.target;
  if (pressedElement.classList.contains('closebtn')) {
    pressedElement.parentNode.remove();
  }
}

function changePreviewForEdit() {
  const grayRows = document.querySelectorAll('.grey-column-invoice-print');
  grayRows.forEach((row) => {
    row.classList = 'grey-column-invoice';
  });
  const blueRow = document.querySelectorAll('.blue-column-invoice-print');
  blueRow.forEach((row) => {
    row.classList = 'blue-column-invoice';
  });
}

function changePreviewForPrint() {
  const grayRows = document.querySelectorAll('.grey-column-invoice');
  grayRows.forEach((row) => {
    row.classList = 'grey-column-invoice-print';
  });
  const blueRow = document.querySelectorAll('.blue-column-invoice');
  blueRow.forEach((row) => {
    row.classList = 'blue-column-invoice-print';
  });
}

function printInvoice() {
  hideEditElements();
  changePreviewForPrint();
  print();
  changePreviewForEdit();
  showEditElements();
}

function hideEditElements() {
  hideNewRowForm();
  hideEditButton();
  hidePrintButton();
}

function showEditElements() {
  showNewRowForm();
  showEditButton();
  showPrintButton();
}

function showEditButton() {
  changeDisplayForEditButton('block');
}

function hideEditButton() {
  changeDisplayForEditButton('none');
}

function changeDisplayForEditButton(editButtonDisplay) {
  getEditButton().style.display = editButtonDisplay;
}

function hideNewRowForm() {
  changeDisplayForNewRowForm('none');
}

function showNewRowForm() {
  changeDisplayForNewRowForm('flex');
}

function changeDisplayForNewRowForm(newRowCreatorDisplay) {
  const newRowCreator = document.querySelector('#new-row');
  newRowCreator.style.display = newRowCreatorDisplay;
}

function hidePrintButton() {
  changeDisplayForPrintButton('none');
}

function showPrintButton() {
  changeDisplayForPrintButton('block');
}

function changeDisplayForPrintButton(buttonPrintDisplay) {
  getPrintButton().style.display = buttonPrintDisplay;
}

function getRows() {
  return document.querySelectorAll('.row-invoice');
}

function getPrintButton() {
  return document.querySelector('#print-button');
}

function getEditButton() {
  return document.querySelector('#container-edit #edit');
}
