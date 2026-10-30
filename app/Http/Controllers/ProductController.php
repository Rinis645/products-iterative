<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        return view('products.show', compact('product'));
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
            'expiration_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:255'],
        ]);
    }
}
