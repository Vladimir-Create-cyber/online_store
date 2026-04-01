@extends('admin.layouts.app')

@section('title', 'Пользователи')

@section('content')
    <h1 class="mb-4">Список пользователей</h1>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">+ Добавить пользователя</a>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3 d-flex align-items-end gap-2">
        <div>
            <label for="role" class="form-label">Фильтр по роли:</label>
            <select name="role" id="role" class="form-select">
                <option value="">Все</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="search" class="form-label">Поиск:</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Имя или Email">
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Применить</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Сбросить</a>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>
                <a href="{{ route('admin.users.index', ['sort' => 'id', 'direction' => request('direction') === 'asc' && request('sort') === 'id' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}">
                    ID {!! request('sort') === 'id' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>Аватар</th>
            <th>
                <a href="{{ route('admin.users.index', ['sort' => 'name', 'direction' => request('direction') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}">
                    Имя {!! request('sort') === 'name' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('admin.users.index', ['sort' => 'email', 'direction' => request('direction') === 'asc' && request('sort') === 'email' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}">
                    Email {!! request('sort') === 'email' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>Роли</th>
            <th>
                <a href="{{ route('admin.users.index', ['sort' => 'created_at', 'direction' => request('direction') === 'asc' && request('sort') === 'created_at' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}">
                    Дата регистрации {!! request('sort') === 'created_at' ? (request('direction') === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>Описание</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <img src="{{ $user->avatar_url }}" alt="Аватар" width="40" height="40" style="border-radius: 50%;">
                </td>
                <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach ($user->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>{{ $user->created_at->format('d.m.Y') }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">Подробнее</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Редактировать</a>

                    <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm {{ $user->is_blocked ? 'btn-success' : 'btn-warning' }}">
                            {{ $user->is_blocked ? 'Разблокировать' : 'Заблокировать' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Пользователи не найдены</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
@endsection
