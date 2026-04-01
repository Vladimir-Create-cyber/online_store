@extends('layouts.app')

@section('content')
    <div class="product-page">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="product-details">
            <h1>{{ $product->name }}</h1>

            <div class="product-images">
                <div class="main-image-container">
                    <img src="{{ asset('storage/' . $product->image) }}"
                         alt="{{ $product->name }}"
                         class="main-image"
                         id="mainProductImage">
                </div>

                <div class="product-gallery-container">
                    <div class="gallery-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Галерея товара
                    </div>

                    @if ($product->images->count() > 0)
                        <div class="product-gallery">
                            @foreach ($product->images as $img)
                                <div class="gallery-item {{ $loop->first ? 'active' : '' }}"
                                     data-image="{{ asset('storage/' . $img->path) }}">
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="Изображение {{ $loop->iteration }}">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="product-info">
                <div class="product-description">
                    <p>{{ $product->description }}</p>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Категория: {{ $product->category->name }}
                    </div>

                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 20l4-4-4-4m0 8V4" />
                        </svg>
                        Средняя оценка:
                        {{ $product->averageRating() }} ★
                    </div>

                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Доставка: 1-3 дня
                    </div>

                </div>

                <div class="product-price-section">
                    <div class="product-price">
                        {{ number_format($product->price, 2) }} <span class="price-currency">грн.</span>
                    </div>

                    <form action="{{ route('cart.add', $product->id) }}" method="POST">
                        @csrf

                        <div class="cart-form">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn minus">-</button>
                                <input type="number" name="quantity" class="quantity-input" value="1" min="1" required>
                                <button type="button" class="quantity-btn plus">+</button>
                            </div>

                            <button type="submit" class="btn-add-to-cart" type="submit">
                                <span>В корзину</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="cart-icon" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M6 21L18 21C19.6569 21 21 19.6569 21 18L21 8C21 6.34315 19.6569 5 18 5L6 5C4.34315 5 3 6.34315 3 8L3 18C3 19.6569 4.34315 21 6 21Z"></path>
                                    <path d="M16 11L12 7L8 11"></path>
                                    <path d="M12 7L12 15"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="product-reviews">
            <h2>Отзывы ({{ $product->reviews()->where('is_approved', true)->count() }})</h2>

            @forelse($product->reviews()->where('is_approved', true)->with('user')->get() as $review)
                <div class="review-item">
                    <strong>{{ $review->user->name }}</strong>
                    <span> — Рейтинг: {{ $review->rating }} ★</span>
                    <p>{{ $review->review }}</p>
                    <small>{{ $review->created_at->format('d.m.Y') }}</small>
                </div>
            @empty
                <p>Пока нет отзывов.</p>
            @endforelse
        </div>

        @auth
            <div class="review-form">
                <h3>Оставить отзыв</h3>

                <form action="{{ route('reviews.store', $product) }}" method="POST">
                    @csrf

                    <label for="rating">Оценка:</label>
                    <select name="rating" id="rating" required>
                        <option value="">Выберите оценку</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} ★</option>
                        @endfor
                    </select>

                    <br>

                    <label for="review">Комментарий:</label><br>
                    <textarea name="review" id="review" rows="4" placeholder="Ваш отзыв...">{{ old('review') }}</textarea>

                    <br>
                    <button type="submit">Отправить</button>
                </form>
            </div>
        @else
            <p><a href="{{ route('login') }}">Войдите</a>, чтобы оставить отзыв.</p>
        @endauth

    </div>

    <script>
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function () {
                document.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
                const mainImage = document.getElementById('mainProductImage');
                mainImage.src = this.dataset.image;
            });
        });

        const minusBtn = document.querySelector('.quantity-btn.minus');
        const plusBtn = document.querySelector('.quantity-btn.plus');
        const quantityInput = document.querySelector('.quantity-input');

        minusBtn?.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });

        plusBtn?.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            quantityInput.value = value + 1;
        });

        quantityInput?.addEventListener('change', () => {
            if (quantityInput.value < 1) quantityInput.value = 1;
        });
    </script>
@endsection
