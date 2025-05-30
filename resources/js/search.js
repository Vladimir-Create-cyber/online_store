/**
 * Search Form Enhancement Module
 *
 * @module SearchForm
 * @description Добавляет интерактивные поведение для поисковых форм:
 * - Визуальные эффекты при фокусе/потере фокуса
 * - Динамическое переключение иконки поиск/очистка
 * - Обработка очистки поля поиска
 * - Поддержка touch-устройств
 *
 * @version 1.1.0
 * @license MIT
 */

/**
 * Инициализирует улучшенное поведение для всех поисковых форм
 * @function initSearchForm
 * @returns {void}
 *
 * @example
 * // Автоматическая инициализация при загрузке DOM
 * document.addEventListener('DOMContentLoaded', initSearchForm);
 *
 * // Или ручная инициализация для динамически добавленных форм
 * initSearchForm();
 */
export function initSearchForm() {
    const searchForms = document.querySelectorAll('.search-form form');

    searchForms.forEach(form => {
        /** @type {HTMLInputElement} */
        const input = form.querySelector('input[type="text"]');

        /** @type {HTMLElement} */
        const icon = form.querySelector('.search-icon');

        // Выходим если нет обязательных элементов
        if (!input || !icon) {
            console.warn('Search form elements not found', form);
            return;
        }

        /**
         * Переключает класс focused для формы
         * @param {boolean} isFocused - Флаг состояния фокуса
         */
        const toggleFocusState = (isFocused) => {
            form.classList.toggle('focused', isFocused);
        };

        /**
         * Очищает поле ввода и возвращает состояние по умолчанию
         */
        const clearSearchInput = () => {
            input.value = '';
            input.focus();
            updateIconState();

            // Имитируем пользовательский ввод для live-поиска
            const inputEvent = new Event('input', { bubbles: true });
            input.dispatchEvent(inputEvent);
        };

        /**
         * Обновляет состояние иконки в зависимости от содержимого поля
         */
        const updateIconState = () => {
            const hasValue = input.value.length > 0;

            if (hasValue) {
                // Режим очистки
                icon.textContent = '✕';
                icon.style.cursor = 'pointer';
                icon.setAttribute('aria-label', 'Очистить поиск');
                icon.addEventListener('click', clearSearchInput);
            } else {
                // Режим поиска
                icon.textContent = '🔍';
                icon.style.cursor = 'default';
                icon.setAttribute('aria-label', 'Поиск');
                icon.removeEventListener('click', clearSearchInput);
            }
        };

        // ========== Настройка обработчиков событий ========== //

        // Фокус/потеря фокуса
        input.addEventListener('focus', () => toggleFocusState(true));
        input.addEventListener('blur', () => toggleFocusState(false));

        // Touch-устройства
        input.addEventListener('touchstart', () => toggleFocusState(true), {
            passive: true
        });

        // Изменение содержимого
        input.addEventListener('input', updateIconState);

        // Инициализация начального состояния
        updateIconState();

        // ========== Очистка ========== //

        /**
         * Удаляет все обработчики событий
         */
        const cleanup = () => {
            input.removeEventListener('focus', () => toggleFocusState(true));
            input.removeEventListener('blur', () => toggleFocusState(false));
            input.removeEventListener('touchstart', () => toggleFocusState(true));
            input.removeEventListener('input', updateIconState);
            icon.removeEventListener('click', clearSearchInput);
        };

        // Автоматическая очистка при удалении формы из DOM (для SPA)
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations, obs) => {
                if (!document.body.contains(form)) {
                    cleanup();
                    obs.disconnect();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });
}

// Автоматическая инициализация при полной загрузке DOM
if (document.readyState !== 'loading') {
    initSearchForm();
} else {
    document.addEventListener('DOMContentLoaded', initSearchForm);
}
