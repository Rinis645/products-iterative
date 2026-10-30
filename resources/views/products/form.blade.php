@if ($errors->any())
    <div class="mb-6 rounded-md border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert">
        <p class="font-medium">Lūdzu, pārbaudiet ievadītos datus.</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">
    <div>
        <label class="mb-2 block text-sm font-medium" for="name">Nosaukums</label>
        <input class="w-full rounded-md border bg-background px-3 py-2" id="name" name="name" type="text" value="{{ old('name', $product->name ?? '') }}" maxlength="255" required>
        @error('name') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="quantity">Daudzums</label>
        <input class="w-full rounded-md border bg-background px-3 py-2" id="quantity" name="quantity" type="number" value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" max="4294967295" step="1" required>
        @error('quantity') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="description">Apraksts</label>
        <textarea class="w-full rounded-md border bg-background px-3 py-2" id="description" name="description" rows="4" maxlength="1000">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="expiration_date">Derīguma termiņš</label>
        <input class="w-full rounded-md border bg-background px-3 py-2" id="expiration_date" name="expiration_date" type="date" value="{{ old('expiration_date', isset($product) ? $product->expiration_date?->format('Y-m-d') : '') }}">
        @error('expiration_date') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="status">Statuss</label>
        <input class="w-full rounded-md border bg-background px-3 py-2" id="status" name="status" type="text" value="{{ old('status', $product->status ?? 'active') }}" maxlength="255" required>
        @error('status') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>
</div>
