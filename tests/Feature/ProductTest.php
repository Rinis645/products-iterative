<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seeder_creates_at_least_twenty_realistic_products(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertDatabaseCount('products', 20);

        $this->assertSame(
            20,
            Product::query()
                ->whereNotNull('name')
                ->whereNotNull('quantity')
                ->whereNotNull('description')
                ->whereNotNull('status')
                ->count(),
        );

        $this->assertSame(
            0,
            Product::query()
                ->whereNotIn('status', ['available', 'low_stock', 'out_of_stock', 'expired'])
                ->count(),
        );
    }

    public function test_create_and_edit_forms_include_product_attributes(): void
    {
        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="expiration_date"', false)
            ->assertSee('name="status"', false)
            ->assertDontSee('name="price"', false);

        $product = Product::create([
            'name' => 'Kafija',
            'quantity' => 12,
            'description' => 'Grauzdēta kafija',
            'expiration_date' => '2027-01-15',
            'status' => 'available',
        ]);

        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('value="2027-01-15"', false);
    }

    public function test_successful_action_flash_is_displayed_by_layout_component(): void
    {
        $this->withSession(['success' => 'Produkts veiksmīgi izveidots.'])
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('role="status"', false)
            ->assertSee('Produkts veiksmīgi izveidots.');
    }

    public function test_products_index_displays_products(): void
    {
        $product = Product::create([
            'name' => 'Kafija',
            'quantity' => 12,
            'description' => 'Grauzdēta kafija',
            'expiration_date' => '2027-01-15',
            'status' => 'available',
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Kafija')
            ->assertSee('12')
            ->assertSee('15.01.2027')
            ->assertSee('available')
            ->assertSee('class="holy-grail"', false)
            ->assertSee('class="holy-grail__main"', false)
            ->assertSee('class="holy-grail__sidebar-nav"', false)
            ->assertSee('aria-label="Kataloga saites"', false)
            ->assertSee('Testa logo — Produkti')
            ->assertSee('Testa reklāma')
            ->assertSee('īpašos produktu piedāvājumus')
            ->assertSee('Testa copyright')
            ->assertSee('class="css-load-test"', false)
            ->assertSee('CSS ielāde darbojas');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Grauzdēta kafija')
            ->assertSee('12')
            ->assertSee('available')
            ->assertSee('15.01.2027');
    }

    public function test_product_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('products.store'), [
            'name' => 'Tēja',
            'quantity' => '25',
            'description' => 'Zaļā tēja',
            'expiration_date' => '2027-06-30',
            'status' => 'available',
        ])->assertRedirect();

        $product = Product::firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Tēja',
            'quantity' => 25,
            'expiration_date' => '2027-06-30',
            'status' => 'available',
        ]);

        $this->put(route('products.update', $product), [
            'name' => 'Zaļā tēja',
            'quantity' => '18',
            'description' => null,
            'expiration_date' => null,
            'status' => 'limited',
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Zaļā tēja',
            'quantity' => 18,
            'description' => null,
            'expiration_date' => null,
            'status' => 'limited',
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
                'quantity' => '-1',
                'status' => '',
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['name', 'quantity', 'status']);

        $this->from(route('products.create'))
            ->followingRedirects()
            ->post(route('products.store'), [
                'name' => '',
                'quantity' => '-1',
                'status' => '',
            ])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('Lūdzu, pārbaudiet ievadītos datus.');

        $this->post(route('products.store'), [
            'name' => 'Nepareizs produkts',
            'quantity' => '1.5',
            'expiration_date' => 'nepareizs datums',
            'status' => 'available',
        ])->assertSessionHasErrors(['quantity', 'expiration_date']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_expiration_date_must_be_within_two_years_when_creating_or_editing(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30'));

        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('min="2025-10-30"', false)
            ->assertSee('max="2028-10-30"', false);

        $this->post(route('products.store'), [
            'name' => 'Derīgs produkts',
            'quantity' => '1',
            'description' => 'Pārbaudes apraksts',
            'expiration_date' => '2028-10-30',
            'status' => 'available',
        ])->assertRedirect();

        $product = Product::firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'expiration_date' => '2028-10-30',
        ]);

        foreach (['2025-10-29', '2028-10-31'] as $expirationDate) {
            $this->from(route('products.create'))
                ->post(route('products.store'), [
                    'name' => 'Nederīgs produkts',
                    'quantity' => '1',
                    'description' => 'Pārbaudes apraksts',
                    'expiration_date' => $expirationDate,
                    'status' => 'available',
                ])
                ->assertSessionHasErrors('expiration_date');

            $this->from(route('products.edit', $product))
                ->put(route('products.update', $product), [
                    'name' => 'Derīgs produkts',
                    'quantity' => '1',
                    'description' => 'Pārbaudes apraksts',
                    'expiration_date' => $expirationDate,
                    'status' => 'available',
                ])
                ->assertSessionHasErrors('expiration_date');
        }

        $this->put(route('products.update', $product), [
            'name' => 'Derīgs produkts',
            'quantity' => '1',
            'description' => 'Pārbaudes apraksts',
            'expiration_date' => '2028-10-30',
            'status' => 'available',
        ])->assertRedirect(route('products.show', $product));
    }

    public function test_product_routes_use_model_binding(): void
    {
        $this->get(route('products.show', 999))
            ->assertNotFound();
    }
}
