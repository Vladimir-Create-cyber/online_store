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

        <h2>Ваш заказ</h2>
        <ul>
            @foreach($cart as $item)
                @php $product = \App\Models\Product::find($item['product_id']); @endphp
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
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        required
                        value="{{ old('full_name') }}"
                    >
                    @error('full_name')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="phone">Телефон <span>*</span></label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        required
                        value="{{ old('phone') }}"
                    >
                    @error('phone')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="address">Адрес <span>*</span></label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        required
                        value="{{ old('address') }}"
                    >
                    @error('address')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="city">Город <span>*</span></label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        required
                        value="{{ old('city') }}"
                    >
                    @error('city')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="country">Страна</label>
                    <input
                        type="text"
                        id="country"
                        name="country"
                        value="{{ old('country') }}"
                    >
                    @error('country')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="postal_code">Почтовый индекс <span>*</span></label>
                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        required
                        value="{{ old('postal_code') }}"
                    >
                    @error('postal_code')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="shipping_method_id">Способ доставки <span>*</span></label>
                    <select
                        id="shipping_method_id"
                        name="shipping_method_id"
                        required
                    >
                        <option value="" disabled {{ old('shipping_method_id') ? '' : 'selected' }}>
                            -- Выберите способ --
                        </option>
                        @foreach($shippingMethods as $method)
                            <option
                                value="{{ $method->id }}"
                                {{ old('shipping_method_id') == $method->id ? 'selected' : '' }}
                            >
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('shipping_method_id')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="payment_method">Способ оплаты <span>*</span></label>
                    <select
                        id="payment_method"
                        name="payment_method"
                        required
                    >
                        <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>
                            -- Выберите способ --
                        </option>
                        <option
                            value="card"
                            {{ old('payment_method') == 'card' ? 'selected' : '' }}
                        >
                            Картой онлайн
                        </option>
                        <option
                            value="cash_on_delivery"
                            {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}
                        >
                            Наложенный платёж
                        </option>
                    </select>
                    @error('payment_method')<div class="error">{{ $message }}</div>@enderror
                </div>

            </div>

            <div class="checkout-actions">
                <button type="submit" class="submit-btn">
                    Завершить оформление
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         class="icon">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
@endsection
