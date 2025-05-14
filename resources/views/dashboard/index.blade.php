<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold">Личный кабинет</h1>
    <p>Добро пожаловать, {{ Auth::user()->name }}!</p>
@endsection
