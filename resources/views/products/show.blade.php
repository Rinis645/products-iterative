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
            <form class="mt-4 space-y-4" method="POST" action="{{ route('products.tags.update', $product) }}" data-tag-form>
                @csrf
                @method('PUT')
                <ul class="flex flex-wrap gap-2" aria-label="Produkta birkas" data-selected-tags>
                    @foreach ($selectedTags as $tag)
                        <li class="flex items-center gap-2 rounded-full border px-3 py-1 text-sm" data-tag-item data-tag-id="{{ $tag->id }}" data-tag-name="{{ $tag->name }}">
                            <span>{{ $tag->name }}</span>
                            <input type="hidden" name="tag_ids[]" value="{{ $tag->id }}">
                            <button class="font-semibold" type="button" aria-label="Dzēst birku {{ $tag->name }}" data-remove-tag>×</button>
                        </li>
                    @endforeach
                    @foreach ($pendingTagNames as $tagName)
                        <li class="flex items-center gap-2 rounded-full border px-3 py-1 text-sm" data-tag-item data-tag-name="{{ $tagName }}">
                            <span>{{ $tagName }}</span>
                            <input type="hidden" name="new_tags[]" value="{{ $tagName }}">
                            <button class="font-semibold" type="button" aria-label="Dzēst birku {{ $tagName }}" data-remove-tag>×</button>
                        </li>
                    @endforeach
                </ul>
                <div>
                    <label class="mb-2 block text-sm font-medium" for="tag-search">Pievienot birku</label>
                    <input class="w-64 rounded-md border bg-background px-3 py-2" id="tag-search" type="text" autocomplete="off" maxlength="50" aria-autocomplete="list" aria-controls="tag-suggestions" aria-expanded="false" data-tag-input data-search-url="{{ route('tags.search') }}">
                    <div class="mt-1 hidden max-h-48 w-64 overflow-auto rounded-md border bg-background" id="tag-suggestions" role="listbox" data-tag-suggestions></div>
                    <p class="mt-1 text-sm text-muted-foreground" aria-live="polite" data-tag-message></p>
                </div>
                <button class="rounded-md border px-4 py-2 text-sm font-medium" type="submit">Update</button>
            </form>
        </div>
    </div>
</x-layout>
