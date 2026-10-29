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
        <label class="mb-2 block text-sm font-medium" for="description">Apraksts</label>
        <textarea class="w-full rounded-md border bg-background px-3 py-2" id="description" name="description" rows="4" maxlength="1000">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="price">Cena (€)</label>
        <input class="w-full rounded-md border bg-background px-3 py-2" id="price" name="price" type="number" value="{{ old('price', $product->price ?? '') }}" min="0" max="99999999.99" step="0.01" required>
        @error('price') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
    </div>
</div>
