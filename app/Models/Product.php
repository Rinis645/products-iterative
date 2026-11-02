<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const MAX_QUANTITY = 4_294_967_295;

    protected $fillable = [
        'name',
        'quantity',
        'description',
        'expiration_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date:Y-m-d',
        ];
    }

    public function increaseQuantity(int $amount): bool
    {
        return $this->changeQuantity($amount, true);
    }

    public function decreaseQuantity(int $amount): bool
    {
        return $this->changeQuantity($amount, false);
    }

    private function changeQuantity(int $amount, bool $increase): bool
    {
        if ($amount <= 0 || $amount > self::MAX_QUANTITY) {
            return false;
        }

        $query = $this->newQuery()->whereKey($this->getKey());

        if ($increase) {
            $query->where('quantity', '<=', self::MAX_QUANTITY - $amount);
        } else {
            $query->where('quantity', '>=', $amount);
        }

        if ($query->increment('quantity', $increase ? $amount : -$amount) !== 1) {
            return false;
        }

        $this->refresh();

        return true;
    }
}
