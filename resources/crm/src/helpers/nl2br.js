import React from 'react';

const nl2br = (text, id = '') => {
  const random = Math.round(Math.random() * 1000000000);

  return text.split('\n').map((item, i) => {
    const key = `nl2br_${random}_${i}_${id}`;

    return (
      <p className="m-b-0" key={key}>
        {item}
      </p>
    );
  });
};

export default nl2br;
