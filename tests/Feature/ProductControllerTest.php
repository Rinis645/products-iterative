<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_product_index_and_create_pages(): void
    {
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
    }

    public function test_guest_can_access_bound_product_show_and_edit_pages(): void
    {
        $product = Product::factory()->create([
            'name' => 'Kontroliera testa produkts',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Kontroliera testa produkts');

        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('Kontroliera testa produkts');
    }

    public function test_guest_can_store_update_and_delete_a_product(): void
    {
        $this->post(route('products.store'), [
            'name' => 'Jauns kontroliera produkts',
            'quantity' => 10,
            'description' => 'Izveidots bez autentifikācijas.',
            'expiration_date' => null,
            'status' => 'available',
        ])->assertRedirect();

        $product = Product::query()
            ->where('name', 'Jauns kontroliera produkts')
            ->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 10,
            'status' => 'available',
        ]);

        $this->put(route('products.update', $product), [
            'name' => 'Atjaunināts kontroliera produkts',
            'quantity' => 7,
            'description' => 'Atjaunināts bez autentifikācijas.',
            'expiration_date' => null,
            'status' => 'limited',
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Atjaunināts kontroliera produkts',
            'quantity' => 7,
            'status' => 'limited',
        ]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_guest_store_action_returns_validation_errors_for_invalid_data(): void
    {
        $this->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => '',
                'quantity' => -1,
                'status' => '',
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['name', 'quantity', 'status']);

        $this->assertDatabaseCount('products', 0);
    }
}
