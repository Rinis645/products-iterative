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
            <h2 class="text-sm font-medium text-muted-foreground">Daudzums</h2>
            <p class="mt-1 text-lg" id="product-quantity">{{ $product->quantity }}</p>
            <p class="mt-2 text-sm" id="quantity-update-message" aria-live="polite"></p>
            <form class="mt-4 flex flex-wrap items-end gap-3" method="POST" action="{{ route('products.adjust-quantity', $product) }}" data-quantity-form data-max-quantity="{{ \App\Models\Product::MAX_QUANTITY }}">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium" for="quantity-amount">Maināmais daudzums</label>
                    <input class="w-32 rounded-md border bg-background px-3 py-2" id="quantity-amount" name="amount" type="number" value="{{ old('amount', 1) }}" min="1" max="{{ \App\Models\Product::MAX_QUANTITY }}" step="1" required>
                </div>
                <button class="rounded-md border px-4 py-2 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50" type="submit" name="direction" value="increase" @disabled($product->quantity >= \App\Models\Product::MAX_QUANTITY)>Palielināt</button>
                <button class="rounded-md border px-4 py-2 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50" type="submit" name="direction" value="decrease" @disabled($product->quantity === 0)>Samazināt</button>
            </form>
        </div>
        <div>
            <h2 class="text-sm font-medium text-muted-foreground">Statuss</h2>
            <p class="mt-1">{{ $product->status }}</p>
        </div>
        <div>
            <h2 class="text-sm font-medium text-muted-foreground">Derīguma termiņš</h2>
            <p class="mt-1">{{ $product->expiration_date?->format('d.m.Y') ?? 'Nav norādīts' }}</p>
        </div>
        @if ($product->description)
            <div>
                <h2 class="text-sm font-medium text-muted-foreground">Apraksts</h2>
                <p class="mt-1 whitespace-pre-line">{{ $product->description }}</p>
            </div>
        @endif
        <div>
            <h2 class="text-sm font-medium text-muted-foreground">Birkas</h2>
            @if ($product->tags->isNotEmpty())
                <ul class="mt-2 flex flex-wrap gap-2" aria-label="Produkta birkas">
                    @foreach ($product->tags as $tag)
                        <li class="rounded-full border px-3 py-1 text-sm">{{ $tag->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-1 text-sm">Produktam vēl nav birku.</p>
            @endif

            <form class="mt-4 flex flex-wrap items-end gap-3" method="POST" action="{{ route('products.tags.store', $product) }}">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium" for="tag-name">Pievienot birku</label>
                    <input class="w-64 rounded-md border bg-background px-3 py-2" id="tag-name" name="name" type="text" value="{{ old('name') }}" maxlength="50" required>
                </div>
                <button class="rounded-md border px-4 py-2 text-sm font-medium" type="submit">Pievienot</button>
            </form>
            @error('name') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
        </div>
    </div>
</x-layout>
