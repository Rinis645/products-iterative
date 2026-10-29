@props(['title' => 'Produkti'])

<!DOCTYPE html>
<html lang="lv">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/products.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground antialiased">
        <header class="border-b bg-card">
            <nav class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <a class="text-lg font-semibold" href="{{ route('products.index') }}">Produkti</a>
                <a class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground" href="{{ route('products.create') }}">Pievienot produktu</a>
            </nav>
        </header>
        <main class="mx-auto max-w-5xl px-6 py-10">
            @if (session('success'))
                <div class="mb-6 rounded-md border border-green-300 bg-green-50 p-4 text-green-900" role="status">
                    {{ session('success') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
