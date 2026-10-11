@props([
    'title' => null,
    'description' => 'Notes on backend engineering, AI tooling and the projects Arkesh Jayakumar builds.',
])

@php
    $pageTitle = $title ? $title.' · '.config('app.name') : config('app.name');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">

    {{ $head ?? '' }}

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="px-5 py-12 antialiased">
    <x-site-header />

    <main {{ $attributes }}>
        {{ $slot }}
    </main>

    <x-site-footer />
</body>
</html>
