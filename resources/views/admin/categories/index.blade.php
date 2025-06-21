@extends('admin.layouts.app')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1>Категории</h1>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Добавить категорию
            </a>
        </div>

        <div class="dashboard-content mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Список категорий</h2>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                            <tr>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Описание</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td data-label="Название">{{ $category->name }}</td>
                                    <td data-label="Slug">{{ $category->slug }}</td>
                                    <td data-label="Описание">{{ $category->description }}</td>
                                    <td data-label="Действия">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-icon btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Удалить категорию?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-icon btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper mt-4">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
