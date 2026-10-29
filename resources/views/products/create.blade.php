<x-layout title="Jauns produkts">
    <h1 class="mb-6 text-3xl font-semibold">Pievienot produktu</h1>
    <form class="max-w-2xl rounded-lg border bg-card p-6" action="{{ route('products.store') }}" method="POST">
        @csrf
        @include('products.form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground" type="submit">Saglabāt</button>
            <a class="rounded-md border px-4 py-2 text-sm font-medium" href="{{ route('products.index') }}">Atcelt</a>
        </div>
    </form>
</x-layout>
