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
                    <label for="description" class="form-label">Описание</label>
                    <textarea name="description" id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="5">{{ old('description') }}</textarea>
                    @error('description')
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
