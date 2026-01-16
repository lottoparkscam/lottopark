export function createGameMessage() {
  const gameMessageElement = document.querySelector('.gameMessage');
  let alertTimeout = null;

  function show(message, scrollToMessage = false) {
    const gameMessageElement = document.querySelector('.gameMessage');
    let alertTimeout = null;

    if (gameMessageElement) {
      gameMessageElement.classList.add('active');
      gameMessageElement.innerHTML = `
            <i class="fa-solid fa-circle-exclamation"></i> ${message}
        `;

      if (alertTimeout) {
        clearTimeout(alertTimeout);
      }

      if (scrollToMessage) {
        const elementPosition = gameMessageElement.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - 50;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }

      alertTimeout = setTimeout(() => {
        gameMessageElement.classList.remove('active');
        alertTimeout = null;
      }, 3000);
    }
  }

  function hide() {
    if (gameMessageElement && gameMessageElement.classList.contains('active')) {
      gameMessageElement.classList.remove('active');

      if (alertTimeout) {
        clearTimeout(alertTimeout);
        alertTimeout = null;
      }
    }
  }

  return {
    show,
    hide
  };
}
