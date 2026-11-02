<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_and_viewed_through_the_application(): void
    {
        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('name="name"', false);

        $this->post(route('products.store'), [
            'name' => 'Integrācijas kafija',
            'quantity' => 12,
            'description' => 'Grauzdētas kafijas pupiņas',
            'expiration_date' => null,
            'status' => 'available',
        ])->assertRedirect();

        $product = Product::query()->where('name', 'Integrācijas kafija')->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 12,
            'description' => 'Grauzdētas kafijas pupiņas',
            'status' => 'available',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Integrācijas kafija')
            ->assertSee('Grauzdētas kafijas pupiņas')
            ->assertSee('12')
            ->assertSee('available');
    }

    public function test_product_can_be_edited_and_the_changes_are_shown(): void
    {
        $product = Product::factory()->create([
            'name' => 'Vecais nosaukums',
            'quantity' => 8,
            'description' => 'Vecais apraksts',
            'status' => 'available',
        ]);

        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('Vecais nosaukums');

        $this->put(route('products.update', $product), [
            'name' => 'Atjaunināts nosaukums',
            'quantity' => 6,
            'description' => 'Atjaunināts apraksts',
            'expiration_date' => null,
            'status' => 'limited',
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Atjaunināts nosaukums',
            'quantity' => 6,
            'description' => 'Atjaunināts apraksts',
            'status' => 'limited',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Atjaunināts nosaukums')
            ->assertSee('Atjaunināts apraksts')
            ->assertSee('6')
            ->assertSee('limited');
    }

    public function test_product_quantity_can_be_increased_and_decreased_through_the_application(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Maināmais daudzums')
            ->assertSee('10');

        $this->post(route('products.adjust-quantity', $product), [
            'direction' => 'increase',
            'amount' => 5,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success', 'Produkta daudzums veiksmīgi palielināts.');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('15');

        $this->post(route('products.adjust-quantity', $product), [
            'direction' => 'decrease',
            'amount' => 4,
        ])->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success', 'Produkta daudzums veiksmīgi samazināts.');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('11');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 11,
        ]);
    }
}
