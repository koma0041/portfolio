const $emojiGallery = document.getElementById('emojiGallery');

function createEmojiItem(emoji) {
    const emojiItem = document.createElement('div');
    emojiItem.classList.add('emoji-item');

    const emojiCharacter = document.createElement('div');
    emojiCharacter.classList.add('emoji-char');
    emojiCharacter.textContent = emoji.char;

    const emojiName = document.createElement('div');
    emojiName.classList.add('emoji-name');
    emojiName.textContent = emoji.name;

    emojiItem.appendChild(emojiCharacter);
    emojiItem.appendChild(emojiName);

    return emojiItem;
}

for (let i = 0; i < emoji.length; i++) {
    const emojiObj = emoji[i];
    const emojiItem = createEmojiItem(emojiObj);
    emojiGallery.appendChild(emojiItem);
}