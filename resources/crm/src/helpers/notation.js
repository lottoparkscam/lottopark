export const capitalize = (value) => value[0].toUpperCase() + value.slice(1);
export const capitalizeWords = (value) =>
  value.replace(/\b\w/g, (firstLetterOfWord) =>
    firstLetterOfWord.toUpperCase(),
  );
