@extends('admin.layouts.app')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Редактировать товар: {{ $product->name }}</h1>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>

        <div class="dashboard-content">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Название товара *</label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->getRawOriginal('name')) }}">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_uk">Название товара (UA)</label>
                            <input type="text" id="name_uk" name="name_uk"
                                   class="form-control @error('name_uk') is-invalid @enderror"
                                   value="{{ old('name_uk', $product->getRawOriginal('name_uk')) }}">
                            @error('name_uk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_en">Название товара (EN)</label>
                            <input type="text" id="name_en" name="name_en"
                                   class="form-control @error('name_en') is-invalid @enderror"
                                   value="{{ old('name_en', $product->getRawOriginal('name_en')) }}">
                            @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price">Цена *</label>
                            <input type="number" id="price" name="price" step="0.01"
                                   class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price', $product->price) }}">
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="stock">Количество на складе *</label>
                            <input type="number" id="stock" name="stock"
                                   class="form-control @error('stock') is-invalid @enderror"
                                   value="{{ old('stock', $product->stock) }}">
                            @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id">Категория *</label>
                            <select name="category_id" id="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Описание *</label>
                            <textarea id="description" name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description', $product->getRawOriginal('description')) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description_uk">Описание (UA)</label>
                            <textarea id="description_uk" name="description_uk"
                                      class="form-control @error('description_uk') is-invalid @enderror"
                                      rows="4">{{ old('description_uk', $product->getRawOriginal('description_uk')) }}</textarea>
                            @error('description_uk')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description_en">Описание (EN)</label>
                            <textarea id="description_en" name="description_en"
                                      class="form-control @error('description_en') is-invalid @enderror"
                                      rows="4">{{ old('description_en', $product->getRawOriginal('description_en')) }}</textarea>
                            @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_new" id="is_new" value="1"
                                {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
                            <label for="is_new" class="form-check-label">Отметить как новинку</label>
                        </div>

                        <div class="form-group">
                            <label for="image">Главное изображение</label>
                            <input type="file" name="image" id="image"
                                   class="form-control-file @error('image') is-invalid @enderror">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Поддержка: JPG, PNG, WEBP. Макс. 5MB
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="additional_images">Дополнительные изображения</label>
                            <input type="file" name="additional_images[]" id="additional_images"
                                   multiple
                                   class="form-control-file @error('additional_images') is-invalid @enderror">
                            @error('additional_images')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Можно выбрать несколько файлов. Поддержка: JPG, PNG, WEBP
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </form>

                    @if($product->image)
                        <hr>
                        <div class="form-group mt-4">
                            <p>Текущее главное изображение:</p>
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 width="150"
                                 class="mb-2 image-preview">
                            <form action="{{ route('admin.products.removeImage', $product) }}" method="POST"
                                  onsubmit="return confirm('Вы уверены, что хотите удалить главное изображение?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Удалить главное изображение
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($product->images && $product->images->count())
                        <hr>
                        <div class="form-group mt-4">
                            <p>Дополнительные изображения:</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                                @foreach ($product->images as $img)
                                    <div style="position: relative;">
                                        <img src="{{ asset('storage/' . $img->path) }}"
                                             alt="Доп. изображение"
                                             style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #ccc;">

                                        <form action="{{ route('admin.products.deleteImage', $img->id) }}" method="POST"
                                              onsubmit="return confirm('Удалить изображение?')"
                                              style="position: absolute; top: 4px; right: 4px;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: red; font-weight: bold;">
                                                ×
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
