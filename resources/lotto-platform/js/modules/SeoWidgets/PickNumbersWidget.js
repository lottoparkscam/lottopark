/**
 * Set data in window object
 * window.normalNumbersCount
 * window.bonusNumbersCount
 */

import Ticket from './Ticket';
import {
  hidePointersOnUnpickedTickets,
  showAllPointers,
  rebuildPlayButtonUrl,
  enablePlayButton,
  disablePlayButton,
  getPlayButton,
} from './UIManipulators';

const ticket = new Ticket();

ticket.setMaxNormalNumbersCount(window.normalNumbersCount);
ticket.setMaxBonusNumbersCount(window.bonusNumbersCount);

ticket.setMaxNormalNumbersExceededCallback(() => {
  hidePointersOnUnpickedTickets(false);
});
ticket.setMaxBonusNumbersExceededCallback(() => {
  hidePointersOnUnpickedTickets(true);
});

ticket.setPickedAllNumbersCallback(enablePlayButton);
ticket.setUnpickedAllNumbersCallback(disablePlayButton);

window.onload = () => {
  document.querySelectorAll('.widget-ticket-numbers > a').forEach((button) => {
    button.addEventListener('click', (event) => {
      pickNumber(ticket, event.target, false);
    });
  });

  document.querySelectorAll('.widget-ticket-bnumbers > a').forEach((button) => {
    button.addEventListener('click', (event) => {
      pickNumber(ticket, event.target, true);
    });
  });

  // To redirect main window when widget is used in iframe
  getPlayButton().addEventListener('click', (event) => {
    event.preventDefault();
    window.top.location.href = event.target.href;
  });
};

export function pickNumber(ticket, button, isBonus) {
  const number = parseInt(button.innerText);

  const toggleCheckClass = () => {
    button.classList.toggle('checked');
  };

  const isCurrentlyPicked = button.classList.contains('checked');
  if (isCurrentlyPicked) {
    const removeNumber = isBonus
      ? ticket.removeBonusNumber
      : ticket.removeNormalNumber;
    removeNumber(number, () => {
      toggleCheckClass();
      showAllPointers(isBonus);
    });
  } else {
    const addNumber = isBonus ? ticket.addBonusNumber : ticket.addNormalNumber;
    addNumber(number, toggleCheckClass);
  }

  rebuildPlayButtonUrl(ticket.normalNumbers, ticket.bonusNumbers);
}
