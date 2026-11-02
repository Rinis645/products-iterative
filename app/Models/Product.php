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

    public function adjustQuantity(int $adjustment): bool
    {
        if ($adjustment === 0 || $adjustment > self::MAX_QUANTITY || $adjustment < -self::MAX_QUANTITY) {
            return false;
        }

        $query = $this->newQuery()->whereKey($this->getKey());

        if ($adjustment > 0) {
            $query->where('quantity', '<=', self::MAX_QUANTITY - $adjustment);
        } else {
            $query->where('quantity', '>=', -$adjustment);
        }

        if ($query->increment('quantity', $adjustment) !== 1) {
            return false;
        }

        $this->refresh();

        return true;
    }
}
