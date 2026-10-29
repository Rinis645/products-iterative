<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_displays_products(): void
    {
        $product = Product::create([
            'name' => 'Kafija',
            'description' => 'Grauzdēta kafija',
            'price' => 8.50,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Kafija')
            ->assertSee('8,50 €');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Grauzdēta kafija');
    }

    public function test_product_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('products.store'), [
            'name' => 'Tēja',
            'description' => 'Zaļā tēja',
            'price' => '4.25',
        ])->assertRedirect();

        $product = Product::firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Tēja',
            'price' => '4.25',
        ]);

        $this->put(route('products.update', $product), [
            'name' => 'Zaļā tēja',
            'description' => null,
            'price' => '5.00',
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Zaļā tēja',
            'description' => null,
            'price' => '5.00',
        ]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_fields_are_validated(): void
    {
        $this->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => '',
                'price' => '-1',
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['name', 'price']);

        $this->post(route('products.store'), [
            'name' => 'Nepareiza cena',
            'price' => '1.999',
        ])->assertSessionHasErrors('price');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_routes_use_model_binding(): void
    {
        $this->get(route('products.show', 999))
            ->assertNotFound();
    }
}
