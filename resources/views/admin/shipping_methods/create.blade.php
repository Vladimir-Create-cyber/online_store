@extends('admin.layouts.app')

@section('title', 'Добавить способ доставки')

@section('content')
    <div class="shipping-methods-container">
        <div class="section-header">
            <h1 class="section-title">Добавить способ доставки</h1>
            <a href="{{ route('admin.shipping_methods.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        <form action="{{ route('admin.shipping_methods.store') }}" method="POST" class="shipping-form">
            @csrf

            <div class="form-card">
                <div class="form-group">
                    <label for="name" class="form-label">
                        Название <span class="required-asterisk">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-control"
                           required value="{{ old('name') }}"
                           placeholder="Введите название способа доставки">
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name_uk" class="form-label">Название (UA)</label>
                    <input type="text" name="name_uk" id="name_uk" class="form-control"
                           value="{{ old('name_uk') }}"
                           placeholder="Введіть назву способу доставки">
                    @error('name_uk')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name_en" class="form-label">Название (EN)</label>
                    <input type="text" name="name_en" id="name_en" class="form-control"
                           value="{{ old('name_en') }}"
                           placeholder="Enter shipping method name">
                    @error('name_en')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="toggle-group">
                        <input type="checkbox" name="is_active" id="is_active"
                               class="toggle-input" {{ old('is_active', true) ? 'checked' : '' }}>
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
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <a href="{{ route('admin.shipping_methods.index') }}" class="btn btn-reset">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@endsection
