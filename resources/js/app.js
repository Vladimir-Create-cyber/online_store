import './bootstrap';
import { initSearchForm } from './search';

// Инициализация компонентов
initSearchForm();

// Для Livewire (если используется)
document.addEventListener('livewire:load', () => {
    initSearchForm();
});

// Для Turbo Drive (если используется)
document.addEventListener('turbo:render', () => {
    initSearchForm();
});
