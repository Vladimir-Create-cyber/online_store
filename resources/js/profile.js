// resources/js/profile.js

export function initProfile() {
    initAvatarEditor();
}

function initAvatarEditor() {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const moveUpBtn = document.getElementById('moveAvatarUp');
    const moveDownBtn = document.getElementById('moveAvatarDown');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', previewAvatar);
    }

    if (moveUpBtn) {
        moveUpBtn.addEventListener('click', () => moveAvatar('up'));
    }

    if (moveDownBtn) {
        moveDownBtn.addEventListener('click', () => moveAvatar('down'));
    }

    // Используем классы вместо inline-стилей
    if (avatarPreview) {
        avatarPreview.addEventListener('mouseenter', () => {
            avatarPreview.classList.add('avatar-preview-hover');
        });

        avatarPreview.addEventListener('mouseleave', () => {
            avatarPreview.classList.remove('avatar-preview-hover');
        });
    }
}

function previewAvatar(event) {
    // ... существующий код
}

function moveAvatar(direction) {
    // ... существующий код
}
