@extends('layouts.app')

@section('content')
    <h1 class="text-center text-2xl font-bold mb-4">Регистрация</h1>
    <form action="{{ route('register.perform') }}" method="POST" class="max-w-md mx-auto">
        @csrf
        <div class="mb-4">
            <label for="name" class="block mb-1">Имя</label>
            <input type="text" name="name" id="name" required class="w-full border px-3 py-2" value="{{ old('name') }}">
            @error('name')
            <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="email" class="block mb-1">Email</label>
            <input type="email" name="email" id="email" required class="w-full border px-3 py-2" value="{{ old('email') }}">
            @error('email')
            <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="block mb-1">Пароль</label>
            <input type="password" name="password" id="password" required class="w-full border px-3 py-2">
            @error('password')
            <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="block mb-1">Подтверждение пароля</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full border px-3 py-2">
        </div>
        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded">
            Зарегистрироваться
        </button>
    </form>
@endsection
