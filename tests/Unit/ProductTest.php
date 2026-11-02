<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_increase_quantity_updates_and_refreshes_the_product(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->assertTrue($product->increaseQuantity(5));
        $this->assertSame(15, $product->quantity);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 15,
        ]);
    }

    public function test_increase_quantity_rejects_invalid_amounts_and_maximum_overflow(): void
    {
        $product = Product::factory()->create(['quantity' => Product::MAX_QUANTITY]);

        $this->assertFalse($product->increaseQuantity(0));
        $this->assertFalse($product->increaseQuantity(-1));
        $this->assertFalse($product->increaseQuantity(Product::MAX_QUANTITY + 1));
        $this->assertFalse($product->increaseQuantity(1));
        $this->assertSame(Product::MAX_QUANTITY, $product->quantity);
    }

    public function test_decrease_quantity_updates_and_refreshes_the_product(): void
    {
        $product = Product::factory()->create(['quantity' => 15]);

        $this->assertTrue($product->decreaseQuantity(5));
        $this->assertSame(10, $product->quantity);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 10,
        ]);
    }

    public function test_decrease_quantity_rejects_invalid_amounts_and_underflow(): void
    {
        $product = Product::factory()->create(['quantity' => 0]);

        $this->assertFalse($product->decreaseQuantity(0));
        $this->assertFalse($product->decreaseQuantity(-1));
        $this->assertFalse($product->decreaseQuantity(Product::MAX_QUANTITY + 1));
        $this->assertFalse($product->decreaseQuantity(1));
        $this->assertSame(0, $product->quantity);
    }
}
