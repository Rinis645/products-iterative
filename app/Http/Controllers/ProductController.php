<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::query()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validatedData($request));

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkts veiksmīgi izveidots.');
    }

    public function show(Product $product): View
    {
        $product->load('tags');

        return view('products.show', compact('product'));
    }

    public function storeTag(Request $request, Product $product): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $tag = Tag::query()->firstOrCreate(['name' => $data['name']]);
        $product->tags()->syncWithoutDetaching([$tag->id]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Birka veiksmīgi pievienota produktam.');
    }

    public function adjustQuantity(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:increase,decrease'],
            'amount' => ['required', 'integer', 'min:1', 'max:'.Product::MAX_QUANTITY],
        ]);

        $changed = $data['direction'] === 'increase'
            ? $product->increaseQuantity((int) $data['amount'])
            : $product->decreaseQuantity((int) $data['amount']);

        if (! $changed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Daudzumu nevar mainīt izvēlētajā virzienā.',
                ], 422);
            }

            return redirect()->route('products.show', $product)
                ->withErrors(['quantity' => 'Daudzumu nevar mainīt izvēlētajā virzienā.'])
                ->withInput();
        }

        $message = $data['direction'] === 'increase'
            ? 'Produkta daudzums veiksmīgi palielināts.'
            : 'Produkta daudzums veiksmīgi samazināts.';

        if ($request->expectsJson()) {
            return response()->json([
                'quantity' => $product->quantity,
                'message' => $message,
            ]);
        }

        return redirect()->route('products.show', $product)->with('success', $message);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validatedData($request));

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkts veiksmīgi atjaunināts.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produkts veiksmīgi izdzēsts.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'description' => ['nullable', 'string', 'max:1000'],
            'expiration_date' => [
                'nullable',
                'date',
                'after_or_equal:'.today()->subYear()->toDateString(),
                'before_or_equal:'.today()->addYears(2)->toDateString(),
            ],
            'status' => ['required', 'string', 'max:255'],
        ]);
    }
}
