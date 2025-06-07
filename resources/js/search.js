/**
 * Search Form Enhancement Module
 *
 * @module SearchForm
 * @description Управляет поведением поисковой формы:
 * - Динамическое переключение иконки поиск/очистка
 * - Визуальные эффекты при взаимодействии
 * - Очистка поля по клику на иконку
 * - Корректная отправка формы даже с пустым запросом
 *
 * @version 2.0.0
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
 */
export function initSearchForm() {
    const searchForms = document.querySelectorAll('.search-form form');

    searchForms.forEach(form => {
        /** @type {HTMLInputElement} */
        const input = form.querySelector('input[type="text"]');

        /** @type {HTMLElement} */
        const icon = form.querySelector('.search-icon');

        // Проверка необходимых элементов
        if (!input || !icon) {
            console.warn('Search form elements not found', form);
            return;
        }

        /**
         * Обновляет состояние иконки в зависимости от содержимого поля
         * @function updateIconState
         * @private
         */
        const updateIconState = () => {
            const hasValue = input.value.length > 0;

            if (hasValue) {
                // Активируем режим очистки
                icon.textContent = '✕';
                icon.classList.add('clear-active');
                icon.setAttribute('aria-label', 'Очистить поиск');
            } else {
                // Возвращаем режим поиска
                icon.textContent = '🔍';
                icon.classList.remove('clear-active');
                icon.setAttribute('aria-label', 'Поиск');
            }
        };

        /**
         * Очищает поле ввода и возвращает фокус
         * @function clearSearchInput
         * @private
         */
        const clearSearchInput = () => {
            input.value = '';
            input.focus();
            updateIconState();

            // Генерируем событие для обновления состояния
            const inputEvent = new Event('input', { bubbles: true });
            input.dispatchEvent(inputEvent);
        };

        /**
         * Обрабатывает клик по иконке
         * @function handleIconClick
         * @private
         * @param {Event} e - Событие клика
         */
        const handleIconClick = (e) => {
            // Предотвращаем всплытие, чтобы не триггерить форму
            e.stopPropagation();

            if (input.value.length > 0) {
                clearSearchInput();
            }
        };

        /**
         * Обрабатывает отправку формы
         * @function handleFormSubmit
         * @private
         * @param {Event} e - Событие отправки
         */
        const handleFormSubmit = (e) => {
            // Для пустого запроса изменяем действие формы
            if (input.value.trim() === '') {
                e.preventDefault();

                // Получаем базовый URL для всех продуктов
                const baseUrl = form.getAttribute('data-base-url') || '/products';

                // Перенаправляем на страницу всех продуктов
                window.location.href = baseUrl;
            }
        };

        // Инициализация состояния иконки
        updateIconState();

        // Настройка обработчиков событий
        input.addEventListener('input', updateIconState);
        icon.addEventListener('click', handleIconClick);
        form.addEventListener('submit', handleFormSubmit);

        // Очистка при демонтаже (для SPA)
        const cleanup = () => {
            input.removeEventListener('input', updateIconState);
            icon.removeEventListener('click', handleIconClick);
            form.removeEventListener('submit', handleFormSubmit);
        };

        // Автоматическая очистка при удалении формы
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(() => {
                if (!document.body.contains(form)) {
                    cleanup();
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });
}

// Автоматическая инициализация при загрузке DOM
if (document.readyState !== 'loading') {
    initSearchForm();
} else {
    document.addEventListener('DOMContentLoaded', initSearchForm);
}
