@extends('admin.layouts.app')

@section('title', 'Редактировать пользователя')

@section('content')
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Имя</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label>Телефон</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
        </div>

        <div class="mb-3">
            <label>Адрес</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
        </div>

        <div class="mb-3">
            <label>Роли</label>
            <select name="roles[]" class="form-select" multiple>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
@endsection
