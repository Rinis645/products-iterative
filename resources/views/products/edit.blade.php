<x-layout title="Rediģēt produktu">
    <h1 class="mb-6 text-3xl font-semibold">Rediģēt produktu</h1>
    <form class="max-w-2xl rounded-lg border bg-card p-6" action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        @include('products.form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground" type="submit">Saglabāt izmaiņas</button>
            <a class="rounded-md border px-4 py-2 text-sm font-medium" href="{{ route('products.show', $product) }}">Atcelt</a>
        </div>
    </form>
</x-layout>
