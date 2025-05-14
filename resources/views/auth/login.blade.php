@extends('layouts.app')

@section('content')
    <h1 class="text-center text-2xl font-bold mb-4">Вход в систему</h1>
    <form action="{{ route('login.perform') }}" method="POST" class="max-w-md mx-auto">
        @csrf
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
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded">
            Войти
        </button>
    </form>
@endsection
