@props(['title' => 'Produkti'])

<!DOCTYPE html>
<html lang="lv">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/css/products.css', 'resources/js/products.js'])
    </head>
    <body class="bg-background text-foreground antialiased">
        <div class="holy-grail">
            <header class="holy-grail__header">
                <div class="holy-grail__header-inner">
                    <a class="text-lg font-semibold" href="{{ route('products.index') }}">Testa logo — Produkti</a>
                </div>
            </header>

            <div class="holy-grail__body">
                <aside class="holy-grail__sidebar" aria-label="Produktu navigācija">
                    <h2 class="mb-4 font-semibold">Katalogs</h2>
                    <nav class="holy-grail__sidebar-nav" aria-label="Kataloga saites">
                        <a href="{{ route('products.index') }}">Visi produkti</a>
                        <a href="{{ route('products.create') }}">Pievienot produktu</a>
                    </nav>
                </aside>

                <main class="holy-grail__main">
                    @if (session('success'))
                        <div class="mb-6 rounded-md border border-green-300 bg-green-50 p-4 text-green-900" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>

                <aside class="holy-grail__sidebar" aria-label="Papildinformācija">
                    <h2 class="mb-2 font-semibold">Testa reklāma</h2>
                    <p class="text-sm text-muted-foreground">Atklājiet mūsu īpašos produktu piedāvājumus!</p>
                </aside>
            </div>

            <footer class="holy-grail__footer">
                <p>Testa copyright &copy; {{ date('Y') }} {{ config('app.name') }}</p>
                <p class="css-load-test">CSS ielāde darbojas</p>
            </footer>
        </div>
    </body>
</html>
