<x-layout title="Produkti">
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-3xl font-semibold">Produkti</h1>
            <p class="mt-2 text-muted-foreground">Pārvaldiet produktu katalogu.</p>
        </div>
        <a class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground" href="{{ route('products.create') }}">Pievienot produktu</a>
    </div>

    <div class="overflow-hidden rounded-lg border bg-card">
        @if ($products->isEmpty())
            <p class="p-6 text-muted-foreground">Produktu vēl nav.</p>
        @else
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 font-medium">Nosaukums</th>
                        <th class="px-6 py-3 font-medium">Cena</th>
                        <th class="px-6 py-3 text-right font-medium">Darbības</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($products as $product)
                        <tr>
                            <td class="px-6 py-4 font-medium">
                                <a class="hover:underline" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                            </td>
                            <td class="px-6 py-4">{{ number_format((float) $product->price, 2, ',', ' ') }} €</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-4">
                                    <a class="underline underline-offset-4" href="{{ route('products.edit', $product) }}">Rediģēt</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" data-confirm="Vai tiešām dzēst šo produktu?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-destructive underline underline-offset-4" type="submit">Dzēst</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t px-6 py-4">{{ $products->links() }}</div>
        @endif
    </div>
</x-layout>
