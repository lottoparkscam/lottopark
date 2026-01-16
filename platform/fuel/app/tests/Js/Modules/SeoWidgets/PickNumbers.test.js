import {pickNumber} from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/PickNumbersWidget.js';
import * as UIManipulators from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/UIManipulators.js';
import Ticket from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/Ticket.js';

let button;
let ticket;
let addNormalNumberMock;
let removeNormalNumberMock;
let addBonusNumberMock;
let removeBonusNumberMock;
let rebuildPlayButtonUrlMock;
let hidePointersOnUnpickedTickets;
let showAllPointers;

beforeEach(() => {
    document.body.innerHTML = `<a id="firstNumber">1</a>`;
    document.body.innerHTML += `<a href="/test/path?numbers=1,2,3&bnumbers=4,5" class="widget-play-now" id="seo-widget-play-button">
        Play now
    </a>`;

    button = document.getElementById('firstNumber');
    button.innerText = '1';

    ticket = new Ticket();
    ticket.setMaxNormalNumbersCount(1);
    ticket.setMaxBonusNumbersCount(1);

    addNormalNumberMock = jest.spyOn(ticket, 'addNormalNumber');
    removeNormalNumberMock = jest.spyOn(ticket, 'removeNormalNumber');
    addBonusNumberMock = jest.spyOn(ticket, 'addBonusNumber');
    removeBonusNumberMock = jest.spyOn(ticket, 'removeBonusNumber');

    rebuildPlayButtonUrlMock = jest.spyOn(UIManipulators, 'rebuildPlayButtonUrl')
        .mockImplementation(() => {});
    hidePointersOnUnpickedTickets = jest.spyOn(UIManipulators, 'hidePointersOnUnpickedTickets')
        .mockImplementation(() => {});
    showAllPointers = jest.spyOn(UIManipulators, 'showAllPointers')
        .mockImplementation(() => {});
});

afterEach(() => {
    jest.clearAllMocks();
});

test('pick normal number', () => {
    // When
    pickNumber(ticket, button, false);

    // Then
    expect(button.classList.contains('checked')).toBeTruthy();

    expect(addNormalNumberMock).toHaveBeenCalledTimes(1);
    expect(removeNormalNumberMock).toHaveBeenCalledTimes(0);

    expect(rebuildPlayButtonUrlMock).toBeCalledWith([1], []);
    expect(rebuildPlayButtonUrlMock).toHaveBeenCalledTimes(1);
});

test('unpick normal number', () => {
    // Given
    button.classList.add('checked');

    // When
    pickNumber(ticket, button, false);

    // Then
    expect(button.classList.contains('checked')).toBeFalsy();

    expect(removeNormalNumberMock).toHaveBeenCalledTimes(1);
    expect(addNormalNumberMock).toHaveBeenCalledTimes(0);

    expect(rebuildPlayButtonUrlMock).toBeCalledWith([], []);
    expect(rebuildPlayButtonUrlMock).toHaveBeenCalledTimes(1);
});

test('pick bonus number', () => {
    // When
    pickNumber(ticket, button, true);

    // Then
    expect(button.classList.contains('checked')).toBeTruthy();

    expect(addBonusNumberMock).toHaveBeenCalledTimes(1);
    expect(removeBonusNumberMock).toHaveBeenCalledTimes(0);

    expect(rebuildPlayButtonUrlMock).toBeCalledWith([], [1]);
    expect(rebuildPlayButtonUrlMock).toHaveBeenCalledTimes(1);
});

test('unpick normal number', () => {
    // Given
    button.classList.add('checked');

    // When
    pickNumber(ticket, button, true);

    // Then
    expect(button.classList.contains('checked')).toBeFalsy();

    expect(removeBonusNumberMock).toHaveBeenCalledTimes(1);
    expect(addBonusNumberMock).toHaveBeenCalledTimes(0);

    expect(rebuildPlayButtonUrlMock).toBeCalledWith([], []);
    expect(rebuildPlayButtonUrlMock).toHaveBeenCalledTimes(1);
});

test('full scenario', () => {
    // Given
    document.body.innerHTML += `<div class="widget-ticket-numbers">
        <a id="number-1">1</a>
        <a id="number-2">2</a>
    </div>`;
    document.body.innerHTML += `<div class="widget-ticket-bnumbers">
        <a id="bonus-number-1">1</a>
    </div>`;

    const normalNumber1 = document.getElementById('number-1');
    normalNumber1.innerText = '1';
    const normalNumber2 = document.getElementById('number-2');
    normalNumber2.innerText = '2';
    const bonusNumber1 = document.getElementById('bonus-number-1');
    bonusNumber1.innerText = '1';
    const playButton = document.getElementById('seo-widget-play-button');

    // When
    window.dispatchEvent(new Event('load'));

    normalNumber1.click();
    expect(normalNumber1.classList.contains('checked')).toBeTruthy();

    normalNumber2.click();
    expect(normalNumber2.classList.contains('checked')).toBeTruthy();

    bonusNumber1.click();
    expect(bonusNumber1.classList.contains('checked')).toBeTruthy();

    // Then
    expect(hidePointersOnUnpickedTickets).toHaveBeenNthCalledWith(1, false);
    expect(hidePointersOnUnpickedTickets).toHaveBeenNthCalledWith(2, true);
    expect(playButton.classList.contains('disabled')).toBeFalsy();

    normalNumber1.click();
    expect(normalNumber1.classList.contains('checked')).toBeFalsy();
    expect(playButton.classList.contains('disabled')).toBeTruthy();

    normalNumber1.click();
    expect(normalNumber1.classList.contains('checked')).toBeTruthy();
    expect(playButton.classList.contains('disabled')).toBeFalsy();

    bonusNumber1.click();
    expect(bonusNumber1.classList.contains('checked')).toBeFalsy();
    expect(playButton.classList.contains('disabled')).toBeTruthy();

    bonusNumber1.click();
    expect(bonusNumber1.classList.contains('checked')).toBeTruthy();
    expect(playButton.classList.contains('disabled')).toBeFalsy();

    expect(showAllPointers).toHaveBeenCalledTimes(2);
})