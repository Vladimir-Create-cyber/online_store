@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h2>Создание пользователя</h2>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Имя</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>

            <div class="mb-3">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Подтверждение пароля</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Телефон</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>

            <div class="mb-3">
                <label>Адрес</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
            </div>

            <div class="mb-3">
                <label>Роли</label>
                <select name="roles[]" class="form-select" multiple>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary">Создать</button>
        </form>
    </div>
@endsection
