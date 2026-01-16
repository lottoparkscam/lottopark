export default class Ticket {
  constructor() {
    this.normalNumbers = [];
    this.bonusNumbers = [];
    this.maxNormalNumbersCount = 0;
    this.maxBonusNumbersCount = 0;
    this.maxNormalNumbersExceededCallback = () => {};
    this.maxBonusNumbersExceededCallback = () => {};
    this.pickedAllNumbersCallback = () => {};
    this.unpickedAllNumbersCallback = () => {};
  }

  setMaxNormalNumbersCount = (count) => {
    this.maxNormalNumbersCount = count;
  };

  setMaxBonusNumbersCount = (count) => {
    this.maxBonusNumbersCount = count;
  };

  setMaxNormalNumbersExceededCallback = (callback) => {
    this.maxNormalNumbersExceededCallback = callback;
  };

  setMaxBonusNumbersExceededCallback = (callback) => {
    this.maxBonusNumbersExceededCallback = callback;
  };

  setPickedAllNumbersCallback = (callback) => {
    this.pickedAllNumbersCallback = callback;
  };

  setUnpickedAllNumbersCallback = (callback) => {
    this.unpickedAllNumbersCallback = callback;
  };

  allNumbersPicked = () => {
    return (
      this.isNormalNumbersCountExceeded() && this.isBonusNumbersCountExceeded()
    );
  };

  isNormalNumbersCountExceeded = () => {
    return this.normalNumbers.length === this.maxNormalNumbersCount;
  };

  isBonusNumbersCountExceeded = () => {
    return this.bonusNumbers.length === this.maxBonusNumbersCount;
  };

  addNormalNumber = (number, callback = () => {}) => {
    if (this.isNormalNumbersCountExceeded()) {
      return;
    }

    this.normalNumbers.push(number);

    callback();

    if (this.isNormalNumbersCountExceeded()) {
      this.maxNormalNumbersExceededCallback();
    }

    if (this.allNumbersPicked()) {
      this.pickedAllNumbersCallback();
    }
  };

  addBonusNumber = (number, callback = () => {}) => {
    if (this.isBonusNumbersCountExceeded()) {
      return;
    }

    this.bonusNumbers.push(number);

    callback();

    if (this.isBonusNumbersCountExceeded()) {
      this.maxBonusNumbersExceededCallback();
    }

    if (this.allNumbersPicked()) {
      this.pickedAllNumbersCallback();
    }
  };

  removeNormalNumber = (number, callback = () => {}) => {
    const index = this.normalNumbers.indexOf(number);
    const numberExists = index !== -1;
    const allNumbersUnpicked = numberExists && this.allNumbersPicked();
    if (numberExists) {
      this.normalNumbers.splice(index, 1);
    }

    if (allNumbersUnpicked) {
      this.unpickedAllNumbersCallback();
    }

    callback();
  };

  removeBonusNumber = (number, callback = () => {}) => {
    const index = this.bonusNumbers.indexOf(number);
    const numberExists = index !== -1;
    const allNumbersUnpicked = numberExists && this.allNumbersPicked();
    if (numberExists) {
      this.bonusNumbers.splice(index, 1);
    }

    if (allNumbersUnpicked) {
      this.unpickedAllNumbersCallback();
    }

    callback();
  };
}
