@extends('admin.layouts.app')

@section('title', 'Редактировать способ доставки')

@section('content')
    <div class="shipping-methods-container">
        <div class="section-header">
            <h1 class="section-title">Редактировать способ доставки</h1>
            <a href="{{ route('admin.shipping_methods.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Ошибки валидации</h4>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.shipping_methods.update', $shippingMethod) }}" method="POST" class="shipping-form">
            @csrf
            @method('PUT')

            <div class="form-card">
                <div class="form-group">
                    <label for="name" class="form-label">
                        Название <span class="required-asterisk">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-control"
                           required value="{{ old('name', $shippingMethod->name) }}"
                           placeholder="Введите название способа доставки">
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="toggle-group">
                        <input type="checkbox" name="is_active" id="is_active"
                               class="toggle-input" {{ old('is_active', $shippingMethod->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="toggle-label">
                            <span class="toggle-handle"></span>
                            <span class="toggle-text">Активен</span>
                        </label>
                    </div>
                    @error('is_active')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Обновить
                </button>
                <a href="{{ route('admin.shipping_methods.index') }}" class="btn btn-reset">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@endsection
