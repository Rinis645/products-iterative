<x-layout :title="$product->name">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a class="text-sm text-muted-foreground underline underline-offset-4" href="{{ route('products.index') }}">← Visi produkti</a>
            <h1 class="mt-4 text-3xl font-semibold">{{ $product->name }}</h1>
        </div>
        <a class="rounded-md border px-4 py-2 text-sm font-medium" href="{{ route('products.edit', $product) }}">Rediģēt</a>
    </div>
    <div class="max-w-2xl space-y-5 rounded-lg border bg-card p-6">
        <div>
            <h2 class="text-sm font-medium text-muted-foreground">Cena</h2>
            <p class="mt-1 text-lg">{{ number_format((float) $product->price, 2, ',', ' ') }} €</p>
        </div>
        @if ($product->description)
            <div>
                <h2 class="text-sm font-medium text-muted-foreground">Apraksts</h2>
                <p class="mt-1 whitespace-pre-line">{{ $product->description }}</p>
            </div>
        @endif
    </div>
</x-layout>
