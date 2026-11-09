<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'buy_price',
        'sell_price',
        'subtotal',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    /**
     * Relationship to Sale.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relationship to Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
