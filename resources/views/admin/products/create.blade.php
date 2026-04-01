@extends('admin.layouts.app')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1>Добавить новый товар</h1>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>

        <div class="dashboard-content mt-4">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="name" class="required-field">Название товара</label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_uk">Название товара (UA)</label>
                            <input type="text" name="name_uk" id="name_uk"
                                   class="form-control @error('name_uk') is-invalid @enderror"
                                   value="{{ old('name_uk') }}">
                            @error('name_uk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_en">Название товара (EN)</label>
                            <input type="text" name="name_en" id="name_en"
                                   class="form-control @error('name_en') is-invalid @enderror"
                                   value="{{ old('name_en') }}">
                            @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price" class="required-field">Цена</label>
                            <input type="number" name="price" id="price"
                                   class="form-control @error('price') is-invalid @enderror"
                                   step="0.01" min="0" value="{{ old('price') }}" required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="stock" class="required-field">Остаток</label>
                            <input type="number" name="stock" id="stock"
                                   class="form-control @error('stock') is-invalid @enderror"
                                   min="0" value="{{ old('stock') }}" required>
                            @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="required-field">Категория</label>
                            <select name="category_id" id="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Выберите категорию --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image">Главное изображение</label>
                            <input type="file" name="image" id="image"
                                   class="form-control-file @error('image') is-invalid @enderror">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Рекомендуемый размер: 800x600px. JPG, PNG, WEBP. Макс: 2MB.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="images">Дополнительные изображения</label>
                            <input type="file" name="images[]" id="images" multiple
                                   class="form-control-file @error('images.*') is-invalid @enderror">
                            @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Можно выбрать несколько. Форматы: JPG, PNG, WEBP. Макс: 2MB каждое.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="description" class="required-field">Описание</label>
                            <textarea name="description" id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description_uk">Описание (UA)</label>
                            <textarea name="description_uk" id="description_uk"
                                      class="form-control @error('description_uk') is-invalid @enderror"
                                      rows="4">{{ old('description_uk') }}</textarea>
                            @error('description_uk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description_en">Описание (EN)</label>
                            <textarea name="description_en" id="description_en"
                                      class="form-control @error('description_en') is-invalid @enderror"
                                      rows="4">{{ old('description_en') }}</textarea>
                            @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_new" id="is_new" value="1"
                                {{ old('is_new') ? 'checked' : '' }}>
                            <label for="is_new" class="form-check-label">Отметить как новинку</label>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-plus-circle mr-2"></i> Добавить товар
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
