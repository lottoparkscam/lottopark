import Ticket from '../../../../../../../resources/lotto-platform/js/modules/SeoWidgets/Ticket.js';

let ticket;

beforeEach(() => {
    // Given
    ticket = new Ticket();
    ticket.setMaxNormalNumbersCount(3);
    ticket.setMaxBonusNumbersCount(3);
});

test('add normal number', () => {
    let wasRun = false;

    // When
    ticket.addNormalNumber(1, () => {
        wasRun = true;
    });

    // Then
    expect(ticket.normalNumbers).toEqual([1]);
    expect(ticket.bonusNumbers).toEqual([]);
    expect(wasRun).toBe(true);
});

test('add bonus number', () => {
    // Given
    let wasRun = false;

    // When
    ticket.addBonusNumber(5, () => {
        wasRun = true;
    });

    // Then
    expect(ticket.normalNumbers).toEqual([]);
    expect(ticket.bonusNumbers).toEqual([5]);
    expect(wasRun).toBe(true);
});

test('remove normal number', () => {
    // When
    ticket.addNormalNumber(1, () => {});
    ticket.addNormalNumber(2, () => {});
    ticket.addNormalNumber(3, () => {});
    ticket.removeNormalNumber(2, () => {});

    // Then
    expect(ticket.normalNumbers).toEqual([1, 3]);
    expect(ticket.bonusNumbers).toEqual([]);
});

test('remove bonus number', () => {
    // When
    ticket.addBonusNumber(1, () => {});
    ticket.addBonusNumber(2, () => {});
    ticket.addBonusNumber(3, () => {});
    ticket.removeBonusNumber(2, () => {});

    // Then
    expect(ticket.bonusNumbers).toEqual([1, 3]);
    expect(ticket.normalNumbers).toEqual([]);
});

test('max limit exceeded', () => {
    // When
    ticket.setMaxNormalNumbersCount(2);
    ticket.setMaxBonusNumbersCount(2);

    expect(ticket.isNormalNumbersCountExceeded()).toBe(false);
    expect(ticket.isBonusNumbersCountExceeded()).toBe(false);

    ticket.addNormalNumber(2, () => {});
    ticket.addNormalNumber(5, () => {});
    ticket.addNormalNumber(7, () => {});

    expect(ticket.isNormalNumbersCountExceeded()).toBe(true);
    expect(ticket.isBonusNumbersCountExceeded()).toBe(false);

    ticket.addBonusNumber(6, () => {});
    ticket.addBonusNumber(7, () => {});
    ticket.addBonusNumber(8, () => {});

    // Then
    expect(ticket.normalNumbers).toEqual([2, 5]);
    expect(ticket.bonusNumbers).toEqual([6, 7]);
    expect(ticket.isNormalNumbersCountExceeded()).toBe(true);
    expect(ticket.isBonusNumbersCountExceeded()).toBe(true);
});

test('max limit exceeded callback', () => {
    // When
    ticket.setMaxNormalNumbersCount(1);
    ticket.setMaxBonusNumbersCount(1);

    let normalSet = false;
    let bonusSet = false;

    ticket.addNormalNumber(2, () => {
        normalSet = true;
    });
    ticket.addBonusNumber(6, () => {
        bonusSet = true;
    });

    // Then
    expect(ticket.isNormalNumbersCountExceeded()).toBe(true);
    expect(ticket.isBonusNumbersCountExceeded()).toBe(true);

    expect(normalSet).toBe(true);
    expect(bonusSet).toBe(true);
});

describe('all numbers picked callback', () => {
    let wasSet;

    beforeEach(() => {
        wasSet = false;

        ticket.setPickedAllNumbersCallback(() => {
            wasSet = true;
        });
        ticket.setUnpickedAllNumbersCallback(() => {
            wasSet = false;
        })
    });

    test('all picked', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addNormalNumber(3);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);
        ticket.addBonusNumber(6);

        // Then
        expect(ticket.allNumbersPicked()).toBe(true);
        expect(wasSet).toBe(true);
    });

    test('missing bonus number', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addNormalNumber(3);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);

        // Then
        expect(ticket.allNumbersPicked()).toBe(false);
        expect(wasSet).toBe(false);
    });

    test('missing normal number', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);
        ticket.addBonusNumber(6);

        // Then
        expect(ticket.allNumbersPicked()).toBe(false);
        expect(wasSet).toBe(false);
    });

    test('after removal normal number', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addNormalNumber(3);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);
        ticket.addBonusNumber(6);
        ticket.removeNormalNumber(1);

        // Then
        expect(ticket.allNumbersPicked()).toBe(false);
        expect(wasSet).toBe(false);
    });

    test('after removal bonus number', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addNormalNumber(3);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);
        ticket.addBonusNumber(6);
        ticket.removeBonusNumber(6);

        // Then
        expect(ticket.allNumbersPicked()).toBe(false);
        expect(wasSet).toBe(false);
    });

    test('after removal and set again', () => {
        // When
        ticket.addNormalNumber(1);
        ticket.addNormalNumber(2);
        ticket.addNormalNumber(3);
        ticket.addBonusNumber(4);
        ticket.addBonusNumber(5);
        ticket.addBonusNumber(6);
        ticket.removeNormalNumber(1);
        ticket.removeBonusNumber(6);
        ticket.addNormalNumber(1);
        ticket.addBonusNumber(6);

        // Then
        expect(ticket.allNumbersPicked()).toBe(true);
        expect(wasSet).toBe(true);
    });
});