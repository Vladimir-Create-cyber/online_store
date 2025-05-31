import './bootstrap';
import { initSearchForm } from './search';
import { initPasswordToggle } from './password-toggle';

// Инициализация компонентов при обычной загрузке
document.addEventListener('DOMContentLoaded', () => {
    initSearchForm();
    initPasswordToggle(); // Добавлено
});

// Для Livewire (если используется)
document.addEventListener('livewire:load', () => {
    initSearchForm();
    initPasswordToggle(); // Добавлено
});

// Для Turbo Drive (если используется)
document.addEventListener('turbo:render', () => {
    initSearchForm();
    initPasswordToggle(); // Добавлено
});
