<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'production_price',
        'selling_price',
        'product_id',
        'order_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'production_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    /**
     * Define a many-to-one relationship with the Product model.
     *
     * This method establishes that an OrderItem belongs to a single product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Define a many-to-one relationship with the Order model.
     *
     * This method establishes that an OrderItem belongs to a single order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
