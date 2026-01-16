import React, { useEffect, useState } from 'react';

const DebouncedInput = ({
  className,
  type,
  defaultValue,
  onChange,
  waitTime,
}) => {
  const [inputValue, setInputValue] = useState(defaultValue || '');

  const underTesting = typeof jest !== 'undefined';
  if (underTesting) {
    // To prevent tests being processed too long
    waitTime = 1;
  }

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      onChange(inputValue);
    }, waitTime || 500);
    return () => clearTimeout(timeoutId);
  }, [inputValue, waitTime || 500]);

  return (
    <input
      type={type || 'text'}
      className={className || ''}
      value={inputValue}
      onChange={(e) => setInputValue(e.target.value)}
    />
  );
};

export default DebouncedInput;
