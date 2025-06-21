@extends('layouts.app')

@section('content')
    <div class="product-page">

        <div class="product-details">
            <h1>{{ $product->name }}</h1>

            <div class="product-images">
                {{-- Основное изображение --}}
                <div class="main-image-container">
                    <img src="{{ asset('storage/' . $product->image) }}"
                         alt="{{ $product->name }}"
                         class="main-image"
                         id="mainProductImage">
                </div>

                {{-- Галерея --}}
                <div class="product-gallery-container">
                    <div class="gallery-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Галерея товара
                    </div>

                    @if ($product->images->count() > 0)
                        <div class="product-gallery">
                            @foreach ($product->images as $img)
                                <div class="gallery-item {{ $loop->first ? 'active' : '' }}"
                                     data-image="{{ asset('storage/' . $img->path) }}">
                                    <img src="{{ asset('storage/' . $img->path) }}"
                                         alt="Изображение {{ $loop->iteration }}">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Информация о товаре --}}
            <div class="product-info">
                <div class="product-description">
                    <p>{{ $product->description }}</p>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Категория: {{ $product->category->name }}
                    </div>

                    <div class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Доставка: 1-3 дня
                    </div>
                </div>

                <div class="product-price-section">
                    <div class="product-price">
                        {{ number_format($product->price, 2) }}
                        <span class="price-currency">грн.</span>
                    </div>

                    <div class="cart-form">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn minus">-</button>
                            <input type="number" class="quantity-input" value="1" min="1">
                            <button type="button" class="quantity-btn plus">+</button>
                        </div>

                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1" id="cartQuantity">
                            <button type="submit" class="btn-add-to-cart">
                                <span>В корзину</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 21L18 21C19.6569 21 21 19.6569 21 18L21 8C21 6.34315 19.6569 5 18 5L6 5C4.34315 5 3 6.34315 3 8L3 18C3 19.6569 4.34315 21 6 21Z"></path>
                                    <path d="M16 11L12 7L8 11"></path>
                                    <path d="M12 7L12 15"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        // Переключение изображений галереи
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                // Удаляем активный класс у всех элементов
                document.querySelectorAll('.gallery-item').forEach(el => {
                    el.classList.remove('active');
                });

                // Добавляем активный класс текущему элементу
                this.classList.add('active');

                // Меняем основное изображение
                const mainImage = document.getElementById('mainProductImage');
                mainImage.src = this.dataset.image;
            });
        });

        // Управление количеством товара
        document.querySelector('.quantity-btn.minus').addEventListener('click', function() {
            const input = document.querySelector('.quantity-input');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                updateCartQuantity();
            }
        });

        document.querySelector('.quantity-btn.plus').addEventListener('click', function() {
            const input = document.querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
            updateCartQuantity();
        });

        document.querySelector('.quantity-input').addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            updateCartQuantity();
        });

        function updateCartQuantity() {
            document.getElementById('cartQuantity').value =
                document.querySelector('.quantity-input').value;
        }
    </script>
@endsection
