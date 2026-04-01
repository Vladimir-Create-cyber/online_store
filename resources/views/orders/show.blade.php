@extends('layouts.app')

@section('title', __('ui.order_details_title', ['id' => $order->id]))

@section('content')
    <div class="order-detail-container">

        <div class="order-header">
            <a href="{{ route('orders.index') }}" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('ui.back_to_orders') }}
            </a>
            <h1>{{ __('ui.order_details_title', ['id' => $order->id]) }}</h1>

            <div class="order-status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending') {{ __('ui.order_status_pending_payment') }} @break
                    @case('processing') {{ __('ui.order_status_processing') }} @break
                    @case('shipped') {{ __('ui.order_status_shipped') }} @break
                    @case('completed') {{ __('ui.order_status_completed') }} @break
                    @case('cancelled') {{ __('ui.order_status_cancelled') }} @break
                    @default {{ $order->status }}
                @endswitch
            </div>
        </div>

        <div class="order-detail-grid">
            <div class="order-info-card">
                <h2 class="info-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    {{ __('ui.order_information') }}
                </h2>
                <div class="info-grid">
                    <div class="info-label">{{ __('ui.created_at') }}:</div>
                    <div class="info-value">{{ $order->created_at->format('d.m.Y H:i') }}</div>

                    <div class="info-label">{{ __('ui.total') }}:</div>
                    <div class="info-value">{{ number_format($order->total, 0, '', ' ') }} {{ __('ui.currency_uah') }}</div>

                    <div class="info-label">{{ __('ui.payment_method') }}:</div>
                    <div class="info-value">{{ $order->payment_method_text }}</div>

                    <div class="info-label">{{ __('ui.shipping_method') }}:</div>
                    <div class="info-value">{{ $order->shipping_method_text }}</div>
                </div>
            </div>

            <div class="order-info-card">
                <h2 class="info-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    {{ __('ui.shipping_address') }}
                </h2>
                <div class="info-grid">
                    <div class="info-label">{{ __('ui.name') }}:</div>
                    <div class="info-value">{{ $order->shippingAddress->full_name ?? __('ui.not_specified') }}</div>

                    <div class="info-label">{{ __('ui.phone') }}:</div>
                    <div class="info-value">{{ $order->shippingAddress->phone ?? __('ui.not_specified') }}</div>

                    <div class="info-label">{{ __('ui.address') }}:</div>
                    <div class="info-value">
                        {{ $order->shippingAddress->address ?? __('ui.not_specified') }},
                        {{ $order->shippingAddress->city ?? '' }},
                        {{ $order->shippingAddress->country ?? '' }}
                    </div>

                    <div class="info-label">{{ __('ui.postal_code') }}:</div>
                    <div class="info-value">{{ $order->shippingAddress->postal_code ?? __('ui.not_specified') }}</div>
                </div>
            </div>
        </div>

        <div class="order-items-card">
            <h2 class="info-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                </svg>
                {{ __('ui.products_in_order') }}
            </h2>

            <div class="order-items-list">
                @foreach($order->orderItems as $item)
                    <div class="order-item">
                        <div class="item-image">
                            @if($item->product)
                                <img src="{{ $item->product->image_url }}"
                                     alt="{{ $item->product_name }}"
                                     style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd;">
                            @else
                                <div class="image-placeholder" style="width: 100px; height: 100px; background: #f2f2f2;"></div>
                            @endif
                        </div>

                        <div class="item-details">
                            <h3 class="item-name">{{ $item->product_name }}</h3>
                            @if($item->variant)
                                <p class="item-variant">{{ $item->variant->name }}</p>
                            @endif
                        </div>

                        <div class="item-quantity">x{{ $item->quantity }}</div>
                        <div class="item-price">{{ number_format($item->price, 0, '', ' ') }} {{ __('ui.currency_uah') }}</div>
                        <div class="item-total">{{ number_format($item->price * $item->quantity, 0, '', ' ') }} {{ __('ui.currency_uah') }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($order->status === 'pending')
            <div class="order-actions">
                <button class="btn-pay">{{ __('ui.pay_order') }}</button>

                <form method="POST" action="{{ route('orders.cancel', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-cancel" onclick="return confirm('{{ __('ui.confirm_cancel_order') }}');">
                        {{ __('ui.cancel_order') }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    @push('scripts')
        <script async
                src="https://pay.google.com/gp/p/js/pay.js"
                onload="onGooglePayLoaded()"></script>

        <script>
            function onGooglePayLoaded() {
                const paymentsClient = new google.payments.api.PaymentsClient({
                    environment: '{{ env("GOOGLE_PAY_ENVIRONMENT", "TEST") }}'
                });

                const paymentDataRequest = {
                    apiVersion: 2,
                    apiVersionMinor: 0,
                    allowedPaymentMethods: [{
                        type: 'CARD',
                        parameters: {
                            allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                            allowedCardNetworks: ['VISA', 'MASTERCARD']
                        },
                        tokenizationSpecification: {
                            type: 'PAYMENT_GATEWAY',
                            parameters: {
                                'gateway': 'stripe',
                                'stripe:publishableKey': '{{ env("STRIPE_PUBLISHABLE_KEY") }}',
                                'stripe:version': '2020-08-27'
                            }
                        }
                    }],
                    merchantInfo: {
                        merchantId: '{{ env("GOOGLE_PAY_MERCHANT_ID") }}',
                        merchantName: '{{ config('app.name') }}'
                    },
                    transactionInfo: {
                        totalPriceStatus: 'FINAL',
                        totalPrice: '{{ number_format($order->total, 2, ".", "") }}',
                        currencyCode: 'UAH',
                        countryCode: 'UA'
                    }
                };

                const button = document.querySelector('.btn-pay');

                button.addEventListener('click', function () {
                    paymentsClient.loadPaymentData(paymentDataRequest)
                        .then(function(paymentData) {
                            const paymentToken = paymentData.paymentMethodData.tokenizationData.token;

                            fetch("{{ route('payment.process') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    order_id: {{ $order->id }},
                                    payment_token: paymentToken
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if(data.success) {
                                        alert('{{ __('ui.payment_successful') }}');
                                        window.location.reload();
                                    } else {
                                        alert('{{ __('ui.payment_error') }}: ' + data.message);
                                    }
                                })
                                .catch(() => alert('{{ __('ui.payment_network_error') }}'));
                        })
                        .catch(err => {
                            console.error('Google Pay error:', err.statusCode, err.statusMessage);
                        });
                });
            }
        </script>
    @endpush

@endsection
