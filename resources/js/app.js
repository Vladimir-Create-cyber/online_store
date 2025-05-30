import './bootstrap';
import { initSearchForm } from './search';

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    initSearchForm();
});

// Для динамически загружаемого контента (если нужно)
window.initSearchForm = initSearchForm;
