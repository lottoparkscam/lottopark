import {hidePointersOnUnpickedTickets, showAllPointers, rebuildPlayButtonUrl, enablePlayButton, disablePlayButton} from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/UIManipulators.js';
import Ticket from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/Ticket.js';

let ticket;

beforeEach(() => {
    // Given
    ticket = new Ticket();
    ticket.setMaxNormalNumbersCount(3);
    ticket.setMaxBonusNumbersCount(2);
});

test('hide pointers on unpicked normal tickets', () => {
    // Given
    document.body.innerHTML = `<div class="widget-ticket-numbers">
        <a>1</a>
        <a class="checked">2</a>
    </div>`;

    // When
    hidePointersOnUnpickedTickets(false);

    // Then
    const elementsWithoutPointersCount = document.querySelectorAll('.withoutPointer').length;
    expect(elementsWithoutPointersCount).toEqual(1);

    const checkedElementHasNoWithoutPointerClass = document.querySelectorAll('.checked.withoutPointer').length === 0;
    expect(checkedElementHasNoWithoutPointerClass).toBeTruthy();
})

test('hide pointers on unpicked bonus tickets', () => {
    // Given
    document.body.innerHTML = `<div class="widget-ticket-bnumbers">
        <a>1</a>
        <a class="checked">2</a>
    </div>`;

    // When
    hidePointersOnUnpickedTickets(true);

    // Then
    const elementsWithoutPointersCount = document.querySelectorAll('.withoutPointer').length;
    expect(elementsWithoutPointersCount).toEqual(1);

    const checkedElementHasNoWithoutPointerClass = document.querySelectorAll('.checked.withoutPointer').length === 0;
    expect(checkedElementHasNoWithoutPointerClass).toBeTruthy();
})

test('show all pointers of normal tickets', () => {
    // Given
    document.body.innerHTML = `<div class="widget-ticket-numbers">
        <a>1</a>
        <a class="withoutPointer">3</a>
        <a class="checked">2</a>
        <a class="checked withoutPointer">2</a>
    </div>`;

    // When
    showAllPointers(false);

    // Then
    const elementsWithoutPointersCount = document.querySelectorAll('.withoutPointer').length;
    expect(elementsWithoutPointersCount).toEqual(0);
})

test('show all pointers of bonus tickets', () => {
    // Given
    document.body.innerHTML = `<div class="widget-ticket-bnumbers">
        <a>1</a>
        <a class="withoutPointer">3</a>
        <a class="checked">2</a>
        <a class="checked withoutPointer">2</a>
    </div>`;

    // When
    showAllPointers(true);

    // Then
    const elementsWithoutPointersCount = document.querySelectorAll('.withoutPointer').length;
    expect(elementsWithoutPointersCount).toEqual(0);
})

test('rebuild play button url', () => {
    // Given
    document.body.innerHTML = `<a href="/test/path?numbers=1,2,3&bnumbers=4,5" class="widget-play-now" id="seo-widget-play-button" disabled>
        Play now
    </a>`;

    // When
    rebuildPlayButtonUrl([6,7,8], [9,10]);

    // Then
    const button = document.getElementById('seo-widget-play-button');
    const expected = '/test/path?numbers=' + encodeURIComponent('6,7,8') + '&bnumbers=' + encodeURIComponent('9,10');
    expect(button.href).toContain(expected);
});

test('enable button play button', () => {
    // Given
    document.body.innerHTML = `<a href="/test/path?numbers=1,2,3&bnumbers=4,5" class="widget-play-now" id="seo-widget-play-button">
        Play now
    </a>`;
    const button = document.getElementById('seo-widget-play-button');
    button.classList.add('disabled');

    // When
    enablePlayButton();

    //Then
    expect(button.classList.contains('disabled')).toBeFalsy();
});

test('disable button play button', () => {
    // Given
    document.body.innerHTML = `<a href="/test/path?numbers=1,2,3&bnumbers=4,5" class="widget-play-now" id="seo-widget-play-button">
        Play now
    </a>`;
    const button = document.getElementById('seo-widget-play-button');
    button.classList.remove('disabled');

    // When
    disablePlayButton();

    //Then
    expect(button.classList.contains('disabled')).toBeTruthy();
});
