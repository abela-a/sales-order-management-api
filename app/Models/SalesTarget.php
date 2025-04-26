<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active_date',
        'amount',
        'sales_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the sales that owns the target.
     *
     * This establishes a one-to-many inverse relationship with the Sales model,
     * where each target belongs to a single sales record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
