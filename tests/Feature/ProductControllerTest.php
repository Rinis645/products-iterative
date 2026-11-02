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

    public function test_guest_can_increase_and_decrease_product_quantity_from_show_page(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Maināmais daudzums')
            ->assertSee('Palielināt')
            ->assertSee('Samazināt');

        $this->post(route('products.adjust-quantity', $product), [
            'direction' => 'increase',
            'amount' => 5,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success', 'Produkta daudzums veiksmīgi palielināts.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 15,
        ]);

        $this->post(route('products.adjust-quantity', $product), [
            'direction' => 'decrease',
            'amount' => 4,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success', 'Produkta daudzums veiksmīgi samazināts.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 11,
        ]);
    }

    public function test_quantity_adjustment_rejects_invalid_values_and_stock_boundaries(): void
    {
        $product = Product::factory()->create(['quantity' => 0]);

        $this->from(route('products.show', $product))
            ->post(route('products.adjust-quantity', $product), [
                'direction' => 'decrease',
                'amount' => 1,
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors(['quantity']);

        $product->update(['quantity' => Product::MAX_QUANTITY]);

        $this->from(route('products.show', $product))
            ->post(route('products.adjust-quantity', $product), [
                'direction' => 'increase',
                'amount' => 1,
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors(['quantity']);

        $this->from(route('products.show', $product))
            ->post(route('products.adjust-quantity', $product), [
                'direction' => 'increase',
                'amount' => 0,
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => Product::MAX_QUANTITY,
        ]);
    }

    public function test_quantity_adjustment_returns_json_for_fetch_requests(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->postJson(route('products.adjust-quantity', $product), [
            'direction' => 'increase',
            'amount' => 5,
        ])->assertOk()
            ->assertJson([
                'quantity' => 15,
                'message' => 'Produkta daudzums veiksmīgi palielināts.',
            ]);

        $this->postJson(route('products.adjust-quantity', $product), [
            'direction' => 'decrease',
            'amount' => 20,
        ])->assertUnprocessable()
            ->assertJson([
                'message' => 'Daudzumu nevar mainīt izvēlētajā virzienā.',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 15,
        ]);
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
