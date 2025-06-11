// Админские скрипты: графики, таблицы
console.log('Admin scripts loaded');

// Инициализация компонентов админки
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация выпадающих меню
    initDropdowns();

    // Инициализация переключателей пароля
    if (typeof initPasswordToggle === 'function') {
        initPasswordToggle();
    }

    // Инициализация графиков
    if (typeof initCharts === 'function') {
        initCharts();
    }

    // Другие общие функции админки
});

// Плавный выход из системы
document.querySelectorAll('.logout-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Анимация перед выходом
        document.body.style.opacity = '0.8';
        document.body.style.transition = 'opacity 0.5s ease';

        setTimeout(() => {
            this.submit();
        }, 500);
    });
});
