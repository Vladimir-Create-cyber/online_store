@extends('admin.layouts.app')

@section('content')
    <div class="category-create container">
        <h1>Добавить категорию</h1>

        <div class="form-card">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label">Название</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required
                           autofocus>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name_uk" class="form-label">Название (UA)</label>
                    <input type="text" name="name_uk" id="name_uk"
                           class="form-control @error('name_uk') is-invalid @enderror"
                           value="{{ old('name_uk') }}">
                    @error('name_uk')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name_en" class="form-label">Название (EN)</label>
                    <input type="text" name="name_en" id="name_en"
                           class="form-control @error('name_en') is-invalid @enderror"
                           value="{{ old('name_en') }}">
                    @error('name_en')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Описание</label>
                    <textarea name="description" id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="5">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description_uk" class="form-label">Описание (UA)</label>
                    <textarea name="description_uk" id="description_uk"
                              class="form-control @error('description_uk') is-invalid @enderror"
                              rows="5">{{ old('description_uk') }}</textarea>
                    @error('description_uk')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description_en" class="form-label">Описание (EN)</label>
                    <textarea name="description_en" id="description_en"
                              class="form-control @error('description_en') is-invalid @enderror"
                              rows="5">{{ old('description_en') }}</textarea>
                    @error('description_en')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus-circle mr-2"></i> Создать категорию
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Назад к списку
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
