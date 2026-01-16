jQuery(window).on('load', function () {
  
  // ELEMENTS
  const canvas = document.getElementById('ka-canvas');
  const elementBallsKeno = document.querySelectorAll('.ticket-line-number.keno-balls-number-thank-you-page.thank-you-page-ball');
  const elementBannerResults = document.querySelector('.keno.platform-alert.platform-alert-success.hide');
  const elementBtnPlayAgain = document.getElementById('button-play-again');
  const elementHeader = document.getElementById('header');
  const elementParagraph = document.getElementById('text-after-results');
  const elementResultBalls = document.querySelectorAll('#ka-results .ka-ball');
  const elementRowBalls = document.getElementById('balls');
  const elementSectionPlayAgain = document.getElementById('play-again');
  const elementTimer = document.getElementById('timer');

  // SETTINGS
  const settingsDomain = location.hostname.replace(/^www./, '');
  const settingsUrl = `https://api.${settingsDomain}/api/internal/draw/by_ticket?ticketToken=${window.ticketToken}`;
  const settingsCanvasAirStreamStrength = .25;
  const settingsCanvasBallCount = 80;
  const settingsCanvasBallFontSize = '11px';
  const settingsCanvasBallRadius = 10;
  const settingsCanvasContainerRadius = 200;
  const settingsCanvasGravity = .2;
  const settingsCanvasAirStreamWidth = settingsCanvasContainerRadius * .8;
  const settingsPathBallImgLarge = '/wp-content/themes/base/images/keno-animation/ball-lg.png';
  const settingsPathBallImgSmall = '/wp-content/themes/base/images/keno-animation/ball-sm.png';

  // DATA
  const dataCanvasBallImage = new Image();
  const dataCanvasCenterX = canvas.width / 2;
  const dataCanvasCenterY = canvas.height / 2;
  const dataCanvasBallsPicked = [];
  const dataKenoResults = [];
  const dataCanvasBallsPool = [];
  let dataCanvasCountBallsToPick = 0;
  let dataCanvasCountBallsPicked = 0;
  let dataCanvasLastTime = 0;
  let dataCanvasAnimationFrameId;
  let isCanvasAnimating = false;
  let isHeaderExists = false;

  // CANVAS
  const ctx = canvas.getContext('2d');
  
  class Ball {
    constructor(number, x, y, dx, dy) {
      this.number = number;
      this.x = x;
      this.y = y;
      this.dx = dx;
      this.dy = dy;
      this.isSelected = false;
      this.isMoving = true;
      this.targetX = null;
      this.targetY = null;
      this.scale = 1;
      this.isTopLayer = false;
      this.isFalling = false;
    }

    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, settingsCanvasBallRadius * this.scale, 0, Math.PI * 2);
      ctx.fillStyle = this.isSelected ? 'red' : '#f244a5';
      ctx.fill();
      ctx.closePath();
      
      dataCanvasBallImage.src = this.isSelected ? settingsPathBallImgLarge : settingsPathBallImgSmall;

      if (dataCanvasBallImage.complete && dataCanvasBallImage.naturalHeight !== 0) {
        ctx.drawImage(
          dataCanvasBallImage,
          this.x - settingsCanvasBallRadius * this.scale,
          this.y - settingsCanvasBallRadius * this.scale,
          settingsCanvasBallRadius * 2 * this.scale,
          settingsCanvasBallRadius * 2 * this.scale
        );
      }

      ctx.fillStyle = 'black';
      ctx.font = `bold ${settingsCanvasBallFontSize} Arial`;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(this.number, this.x, this.y);
    }

    update(deltaTime) {
      if (this.isFalling) {
        this.dy += settingsCanvasGravity * deltaTime;
        this.x += this.dx * deltaTime;
        this.y += this.dy * deltaTime;

        for (let other of dataCanvasBallsPool) {
          if (other !== this) {
            const dx = other.x - this.x;
            const dy = other.y - this.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            if (distance < settingsCanvasBallRadius * 2 && Math.random() < .5) {
              const angle = Math.atan2(dy, dx);
              const sin = Math.sin(angle);
              const cos = Math.cos(angle);

              const vx1 = this.dx * cos + this.dy * sin;
              const vy1 = this.dy * cos - this.dx * sin;
              const vx2 = other.dx * cos + other.dy * sin;
              const vy2 = other.dy * cos - other.dx * sin;

              const temp_vx1 = vx2;
              const temp_vx2 = vx1;

              this.dx = temp_vx1 * cos - vy1 * sin;
              this.dy = vy1 * cos + temp_vx1 * sin;
              other.dx = temp_vx2 * cos - vy2 * sin;
              other.dy = vy2 * cos + temp_vx2 * sin;

              const overlap = settingsCanvasBallRadius * 2 - distance;
              this.x -= overlap / 2 * Math.cos(angle);
              this.y -= overlap / 2 * Math.sin(angle);
              other.x += overlap / 2 * Math.cos(angle);
              other.y += overlap / 2 * Math.sin(angle);
            }
          }
        }

        const distanceFromCenter = Math.sqrt(Math.pow(this.x - dataCanvasCenterX, 2) + Math.pow(this.y - dataCanvasCenterY, 2));
        if (distanceFromCenter + settingsCanvasBallRadius > settingsCanvasContainerRadius) {
          const angle = Math.atan2(this.y - dataCanvasCenterY, this.x - dataCanvasCenterX);
          this.x = dataCanvasCenterX + (settingsCanvasContainerRadius - settingsCanvasBallRadius) * Math.cos(angle);
          this.y = dataCanvasCenterY + (settingsCanvasContainerRadius - settingsCanvasBallRadius) * Math.sin(angle);
          this.dy *= -0.6;
          this.dx *= 0.85;
        }
      } else {
        if (this.y > dataCanvasCenterY && Math.abs(this.x - dataCanvasCenterX) < settingsCanvasAirStreamWidth / 2) {
          const airEffect = 1 - Math.abs(this.x - dataCanvasCenterX) / (settingsCanvasAirStreamWidth / 2);
          this.dy -= settingsCanvasAirStreamStrength * airEffect * deltaTime * 2;
          this.dx += (Math.random() - 0.5) * settingsCanvasAirStreamStrength * deltaTime;
        }

        this.dy += settingsCanvasGravity * deltaTime;

        const distance = Math.sqrt(Math.pow(this.x - dataCanvasCenterX, 2) + Math.pow(this.y - dataCanvasCenterY, 2));
        if (distance + settingsCanvasBallRadius > settingsCanvasContainerRadius) {
          const angle = Math.atan2(this.y - dataCanvasCenterY, this.x - dataCanvasCenterX);
          this.x = dataCanvasCenterX + (settingsCanvasContainerRadius - settingsCanvasBallRadius) * Math.cos(angle);
          this.y = dataCanvasCenterY + (settingsCanvasContainerRadius - settingsCanvasBallRadius) * Math.sin(angle);
          
          const normal = { x: Math.cos(angle), y: Math.sin(angle) };
          const dot = this.dx * normal.x + this.dy * normal.y;
          this.dx -= 2 * dot * normal.x;
          this.dy -= 2 * dot * normal.y;

          this.dx *= 0.85;
          this.dy *= 0.9;
        }

        if (this.targetX !== null && this.targetY !== null) {
          const speed = 0.05;
          this.x += (this.targetX - this.x) * speed;
          this.y += (this.targetY - this.y) * speed;

          if (this.scale < 2) {
            this.scale += 0.02;
          }

          if (Math.abs(this.x - this.targetX) < 2 && Math.abs(this.y - this.targetY) < 2) {
            this.isMoving = false;
            this.x = this.targetX;
            this.y = this.targetY;
          }
        } else {
          this.x += this.dx * deltaTime;
          this.y += this.dy * deltaTime;
        }

          // kolizje
          if (!this.isSelected) {
            for (let other of dataCanvasBallsPool) {
              if (other !== this && !other.isSelected) {
                const dx = other.x - this.x;
                const dy = other.y - this.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < settingsCanvasBallRadius * 2 && Math.random() < 0.5) {
                  const angle = Math.atan2(dy, dx);
                  const sin = Math.sin(angle);
                  const cos = Math.cos(angle);

                  const vx1 = this.dx * cos + this.dy * sin;
                  const vy1 = this.dy * cos - this.dx * sin;
                  const vx2 = other.dx * cos + other.dy * sin;
                  const vy2 = other.dy * cos - other.dx * sin;

                  const temp_vx1 = vx2;
                  const temp_vx2 = vx1;

                  this.dx = temp_vx1 * cos - vy1 * sin;
                  this.dy = vy1 * cos + temp_vx1 * sin;
                  other.dx = temp_vx2 * cos - vy2 * sin;
                  other.dy = vy2 * cos + temp_vx2 * sin;

                  const overlap = settingsCanvasBallRadius * 2 - distance;
                  this.x -= overlap / 2 * Math.cos(angle);
                  this.y -= overlap / 2 * Math.sin(angle);
                  other.x += overlap / 2 * Math.cos(angle);
                  other.y += overlap / 2 * Math.sin(angle);
                }
              }
            }
          }
      }
    }

    moveToCenter() {
      this.targetX = dataCanvasCenterX;
      this.targetY = dataCanvasCenterY;
      this.isSelected = true;
      this.isTopLayer = true;
    }

    startFalling() {
      this.isFalling = true;
      this.dy = 1;
    }
  }

  function generateBalls() {
    for (let i = 1; i <= settingsCanvasBallCount; i++) {
      const x = dataCanvasCenterX;
      const y = dataCanvasCenterY;
      const angle = Math.random() * Math.PI * 2;
      const speed = 2 + Math.random();
      const dx = Math.cos(angle) * speed;
      const dy = Math.sin(angle) * speed;

      dataCanvasBallsPool.push(new Ball(i, x, y, dx, dy));
    }
  }

  function drawContainer() {
    ctx.beginPath();
    ctx.arc(dataCanvasCenterX, dataCanvasCenterY, settingsCanvasContainerRadius, 0, Math.PI * 2);
    ctx.strokeStyle = 'transparent';
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.closePath();
  }

  function drawResults() {
    dataCanvasBallsPicked.forEach(function(number, i) {
      elementResultBalls[i].setAttribute('data-number', number);

      setTimeout(() => {
        elementResultBalls[i].style.transform = 'scale(1.2)';
      }, 50);
      setTimeout(() => {
        elementResultBalls[i].style.transform = 'scale(1)';
      }, 300);
    });
  }

  function pickBall() {
    if (dataKenoResults.length > 0 && !isCanvasAnimating) {
      const nextBallNumber = dataKenoResults.shift();
      dataCanvasBallsPicked.push(nextBallNumber);

      const pickedBall = dataCanvasBallsPool.find(b => b.number === nextBallNumber);
      if (pickedBall) {
        isCanvasAnimating = true;

        pickedBall.moveToCenter();

        setTimeout(() => {
          dataCanvasBallsPool.splice(dataCanvasBallsPool.indexOf(pickedBall), 1);
          drawResults();
          isCanvasAnimating = false;
          dataCanvasCountBallsPicked++;

          if (dataCanvasCountBallsPicked === dataCanvasCountBallsToPick) {
            dataCanvasBallsPool.forEach(ball => ball.startFalling());
            setTimeout(() => {
              cancelAnimationFrame(dataCanvasAnimationFrameId);
            }, 4000);
          }
        }, 3000);
      }
    }
  }

  function animate(currentTime = 0) {
    if (!dataCanvasLastTime) {
      dataCanvasLastTime = currentTime;
    }
    const deltaTime = Math.min((currentTime - dataCanvasLastTime) / 16.67, 1); // Normalizowanie do 60 FPS
    dataCanvasLastTime = currentTime;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawContainer();

    dataCanvasBallsPool.forEach(ball => {
      if (!ball.isTopLayer) {
        ball.update(deltaTime);
        ball.draw();
      }
    });

    dataCanvasBallsPool.forEach(ball => {
      if (ball.isTopLayer) {
        ball.update(deltaTime);
        ball.draw();
      }
    });

    dataCanvasAnimationFrameId = requestAnimationFrame(animate);
  }

  if (canvas.dataset.enabled == 1) {
    generateBalls();
    animate();
  }

  const timeInterval = setInterval(function () {
    const seconds = document.querySelector('#seconds').innerHTML;
    const minutes = document.querySelector('#minutes').innerHTML;
    const counterIsStillCounting = minutes !== '00' || seconds !== '00';
    if (counterIsStillCounting) {
      return;
    }

    const timeLeftAndHeaderExists = !counterIsStillCounting && isHeaderExists === false;
    if (timeLeftAndHeaderExists) {
      elementTimer.style.display = 'none';
      elementHeader.innerHTML += `<span class="loading"></span><div class="ka-text-pending">${window.resultHeader}</div>`;
      isHeaderExists = true;
    }
    setTimeout(function () {
      const interval = setInterval(function () {
        fetch(settingsUrl, { credentials: 'include' })
          .then((response) => response.json())
          .then((data) => {
            if (data.length === 0) {
              return;
            }
            elementSectionPlayAgain.style.display = 'block';
            //Handles sorting of numbers in ascending order
            const drawNumbers = data.numbers.split(',').sort(function (a, b) {
              return a - b;
            });
            elementRowBalls.innerHTML = '';
            dataKenoResults.length = 0;

            drawNumbers.forEach((number) => {
              elementRowBalls.innerHTML += `<div class="ticket-line-number">${number}</div>`;
              dataKenoResults.push(parseInt(number));
            });
            dataCanvasCountBallsToPick = dataKenoResults.length;

            elementBallsKeno.forEach((item) => {
              const isLostNumber = !drawNumbers.includes(item.innerHTML);
              if (isLostNumber) {
                item.style.opacity = 0.5;
              }
            });
            elementParagraph.innerHTML = window.ticketStatusWin === data.status ? window.messageWin : window.messageLose;
            elementBannerResults.style.display = 'block';
            elementBtnPlayAgain.innerHTML = `<a href="${window.kenoUrl}" class="btn btn-primary">${window.buttonLabel}</a>`;
            clearInterval(interval);
            pickBall();
            setInterval(pickBall, 2000);
            elementHeader.innerHTML = '';
          });
      }, 5000);
    }, 60000);
    clearInterval(timeInterval);
  }, 1000);
});
