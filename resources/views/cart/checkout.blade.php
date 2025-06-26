@extends('layouts.app')

@section('title', 'Оформление заказа')

@section('content')
    <div class="premium-checkout">
        <h1 class="checkout-title">Оформление заказа</h1>

        @if($errors->any())
            <div class="checkout-alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Блок отображения корзины --}}
        <h2>Ваш заказ</h2>
        <ul>
            @foreach($cart as $item)
                @php
                    $product = \App\Models\Product::find($item['product_id']);
                @endphp
                <li>
                    {{ $item['name'] }} — {{ $item['quantity'] }} шт.
                    @if($product && $product->stock >= $item['quantity'])
                        <span class="text-green-600">В наличии.</span>
                    @else
                        <span class="text-red-600">Нет в наличии.</span>
                    @endif
                </li>
            @endforeach
        </ul>


        <form action="{{ route('cart.complete') }}" method="POST" class="checkout-form">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Имя и фамилия <span>*</span></label>
                    <input type="text" id="full_name" name="full_name" required value="{{ old('full_name') }}">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон <span>*</span></label>
                    <input type="tel" id="phone" name="phone" required value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="address">Адрес <span>*</span></label>
                    <input type="text" id="address" name="address" required value="{{ old('address') }}">
                </div>

                <div class="form-group">
                    <label for="city">Город <span>*</span></label>
                    <input type="text" id="city" name="city" required value="{{ old('city') }}">
                </div>

                <div class="form-group">
                    <label for="country">Страна</label>
                    <input type="text" id="country" name="country" value="{{ old('country') }}">
                </div>

                <div class="form-group">
                    <label for="postal_code">Почтовый индекс <span>*</span></label>
                    <input type="text" id="postal_code" name="postal_code" required value="{{ old('postal_code') }}">
                </div>

                <div class="form-group">
                    <label for="shipping_method_code">Способ доставки <span>*</span></label>
                    <select id="shipping_method_code" name="shipping_method_code" required>
                        <option value="">-- Выберите способ --</option>
                        @foreach($shippingMethods as $method)
                            <option value="{{ $method->code }}" {{ old('shipping_method_code') == $method->code ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>



                <div class="form-group">
                    <label for="payment_method">Способ оплаты <span>*</span></label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">-- Выберите способ --</option>
                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Картой онлайн</option>
                        <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>Наложенный платёж</option>
                    </select>
                </div>
            </div>

            <div class="checkout-actions">
                <button type="submit" class="submit-btn">
                    <span>Завершить оформление</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
@endsection
