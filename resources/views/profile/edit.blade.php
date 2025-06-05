@extends('layouts.app')

@section('title', 'Редактирование профиля')

@section('content')
    <div class="profile-container">
        <h1 class="profile-title">Редактирование профиля</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="avatar">Аватар:</label>
                <input type="file" id="avatar" name="avatar" accept="image/*" class="form-input-file">
            </div>

            <div class="avatar-editor">
                <div class="avatar-preview-container">
                    <div class="avatar-frame">
                        <img id="avatarPreview"
                             src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                             alt="Аватар {{ Auth::user()->name }}"
                             class="avatar-preview">
                    </div>
                    <div class="avatar-controls">
                        <button type="button" class="avatar-btn" id="moveAvatarUp">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            Вверх
                        </button>
                        <button type="button" class="avatar-btn" id="moveAvatarDown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            Вниз
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary submit-btn">Сохранить изменения</button>
        </form>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/profile.js'])
@endsection
