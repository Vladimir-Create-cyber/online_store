import './bootstrap';
import { initSearchForm } from './search';
import { initPasswordToggle } from './password-toggle';
import { initProfile } from './profile';

// Инициализация компонентов
document.addEventListener('DOMContentLoaded', () => {
    initSearchForm();
    initPasswordToggle();
    initProfile(); // Инициализация профиля
});

// Для Livewire
document.addEventListener('livewire:load', () => {
    initSearchForm();
    initPasswordToggle();
    initProfile();
});

// Для Turbo Drive
document.addEventListener('turbo:render', () => {
    initSearchForm();
    initPasswordToggle();
    initProfile();
});
