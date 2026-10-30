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
