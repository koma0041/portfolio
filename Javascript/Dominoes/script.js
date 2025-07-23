function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function createTopDots(topDots) {
  const topPart = document.createElement('div');
  topPart.classList.add('topPart');
  topPart.classList.add(`dots-${topDots}`);
  for (let d = 0; d < topDots; d++) {
    const topDot = document.createElement('div');
    topDot.classList.add('dot');
    topPart.append(topDot);
  }
  return topPart;
}

function createBottomDots(bottomDots) {
  const bottomPart = document.createElement('div');
  bottomPart.classList.add('bottomPart');
  bottomPart.classList.add(`dots-${bottomDots}`);
  for (let d = 0; d < bottomDots; d++) {
    const bottomDot = document.createElement('div');
    bottomDot.classList.add('dot');
    bottomPart.append(bottomDot);
  }
  return bottomPart;
}

function createRandomDomino(count) {
  const $body = document.body;
  const dominoContainer = document.createElement('div');
  dominoContainer.id = 'dominoContainer';
  dominoContainer.classList.add('domino-container');
  $body.append(dominoContainer);

  for (let i = 0; i < count; i++) {
    const domino = document.createElement('div');
    domino.classList.add('domino');
    const topDots = randomInt(0, 6);
    const bottomDots = randomInt(0, 6);
    const topPart = createTopDots(topDots);
    const bottomPart = createBottomDots(bottomDots);

    domino.append(topPart);
    domino.append(bottomPart);
    dominoContainer.append(domino); 
  }
}

createRandomDomino(50);
