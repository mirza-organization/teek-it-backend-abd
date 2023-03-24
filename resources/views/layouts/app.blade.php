<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('js/jquery.form.js') }}" defer></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <!-- Bootstrap 5 Css -->
    <link href="{{ asset('bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    {{-- {{dd(Auth::user())}} --}}
    @if (Auth::user()->role_id === 1)
        @include('layouts.admin.app')
    {{-- @elseif (Auth::user()->role_id === 'admin')
        @include('livewire.admin.layout.header') --}}
    @endif
    <!-- Livewire component will by default render here -->
    <main>
        {{ $slot }}
    </main>
    {{-- @if (Auth::user()->role_id === 'emp')
        @include('livewire.employee.layout.footer')
    @elseif (Auth::user()->role_id === 'admin')
        @include('livewire.admin.layout.footer')
    @endif --}}
</body>
<!-- Scripts -->
<!-- Bootstrap 5 -->
<script src="{{ asset('bootstrap5/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
@yield('scripts')

</html>
