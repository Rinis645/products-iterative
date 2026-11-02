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

    public function test_tags_can_be_added_to_a_product_and_shared_with_other_products(): void
    {
        $firstProduct = Product::factory()->create();
        $secondProduct = Product::factory()->create();

        $this->get(route('products.show', $firstProduct))
            ->assertOk()
            ->assertSee('Pievienot birku')
            ->assertSee('Produktam vēl nav birku.');

        $this->post(route('products.tags.store', $firstProduct), [
            'name' => 'Pārtika',
        ])->assertRedirect(route('products.show', $firstProduct))
            ->assertSessionHas('success', 'Birka veiksmīgi pievienota produktam.');

        $this->post(route('products.tags.store', $firstProduct), [
            'name' => 'Atlaide',
        ])->assertRedirect(route('products.show', $firstProduct));

        $this->post(route('products.tags.store', $secondProduct), [
            'name' => 'Pārtika',
        ])->assertRedirect(route('products.show', $secondProduct));

        $this->post(route('products.tags.store', $firstProduct), [
            'name' => 'Pārtika',
        ])->assertRedirect(route('products.show', $firstProduct));

        $this->assertDatabaseCount('tags', 2);
        $this->assertDatabaseCount('product_tag', 3);
        $this->assertSame(
            ['Atlaide', 'Pārtika'],
            $firstProduct->fresh()->tags()->orderBy('name')->pluck('name')->all(),
        );
        $this->assertSame(
            ['Pārtika'],
            $secondProduct->fresh()->tags()->pluck('name')->all(),
        );

        $this->get(route('products.show', $firstProduct))
            ->assertOk()
            ->assertSee('Atlaide')
            ->assertSee('Pārtika');

        $this->get(route('products.show', $secondProduct))
            ->assertOk()
            ->assertSee('Pārtika');
    }

    public function test_tag_name_is_required_and_limited_to_fifty_characters(): void
    {
        $product = Product::factory()->create();

        $this->from(route('products.show', $product))
            ->post(route('products.tags.store', $product), ['name' => '   '])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors('name');

        $this->from(route('products.show', $product))
            ->post(route('products.tags.store', $product), ['name' => str_repeat('a', 51)])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tags', 0);
    }
}
