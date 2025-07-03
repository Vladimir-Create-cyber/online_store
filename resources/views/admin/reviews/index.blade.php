@extends('admin.layouts.app')

@section('content')
    <div class="reviews-container">
        <div class="section-header">
            <h1 class="section-title">Отзывы пользователей</h1>

            <!-- Фильтр -->
            <div class="filter-bar">
                <form method="GET" action="{{ route('admin.reviews.index') }}" class="filter-form">
                    <label for="status">Фильтр:</label>
                    <select name="status" id="status" onchange="this.form.submit()" class="ml-2 border px-2 py-1 rounded">
                        <option value="" {{ $status === null ? 'selected' : '' }}>Все</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Ожидают</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Одобренные</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Уведомления -->
        @if (session('success'))
            <div class="alert alert-success">
                <i class="alert-icon">✓</i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Таблица отзывов -->
        @if ($reviews->isEmpty())
            <div class="empty-state">
                <p>Отзывов пока нет.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="reviews-table">
                    <thead>
                    <tr class="table-dark">
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Товар</th>
                        <th>Оценка</th>
                        <th>Отзыв</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reviews as $review)
                        <tr data-review-id="{{ $review->id }}">
                            <td data-label="ID">{{ $review->id }}</td>
                            <td data-label="Пользователь">{{ $review->user->name }}</td>
                            <td data-label="Товар">
                                <a href="{{ route('product.show', $review->product->slug) }}" target="_blank" class="text-blue-500 underline">
                                    {{ $review->product->name }}
                                </a>
                            </td>
                            <td data-label="Оценка">{{ $review->rating }}</td>
                            <td data-label="Отзыв">{{ $review->review }}</td>
                            <td data-label="Статус">
                                @if ($review->is_approved)
                                    <span class="badge badge-success">Одобрен</span>
                                @else
                                    <span class="badge badge-warning">Ожидает</span>
                                @endif
                            </td>
                            <td data-label="Дата">{{ $review->created_at->format('d.m.Y H:i') }}</td>
                            <td data-label="Действия">
                                <div class="actions-group">
                                    @if (!$review->is_approved)
                                        <button
                                            class="btn-action approve-btn"
                                            data-id="{{ $review->id }}"
                                            data-url="{{ route('admin.reviews.approve', $review->id) }}"
                                        >
                                            Одобрить
                                        </button>
                                    @endif
                                    <button
                                        class="btn-action delete-btn"
                                        data-id="{{ $review->id }}"
                                        data-url="{{ route('admin.reviews.destroy', $review->id) }}"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $reviews->appends(['status' => $status])->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Одобрение отзыва
            document.querySelectorAll('.approve-btn').forEach(button => {
                button.addEventListener('click', function () {
                    if (!confirm('Одобрить отзыв?')) return;

                    fetch(this.dataset.url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                        .then(res => res.json())
                        .then(() => {
                            const row = this.closest('tr');
                            const statusCell = row.querySelector('.badge');

                            // Обновляем статус
                            statusCell.textContent = 'Одобрен';
                            statusCell.className = 'badge badge-success';

                            // Удаляем кнопку одобрения
                            this.remove();
                        })
                        .catch(() => alert('Ошибка при одобрении отзыва.'));
                });
            });

            // Удаление отзыва
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    if (!confirm('Удалить отзыв?')) return;

                    fetch(this.dataset.url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                        .then(res => res.json())
                        .then(() => {
                            this.closest('tr').remove();
                        })
                        .catch(() => alert('Ошибка при удалении отзыва.'));
                });
            });
        });
    </script>
@endpush
