<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'area_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Get the user that owns the sale.
     *
     * This establishes a one-to-many inverse relationship with the User model,
     * where each sale belongs to a single user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the area that owns the sale.
     *
     * This establishes a one-to-many inverse relationship with the Area model,
     * where each sale belongs to a single area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(SalesArea::class);
    }

    /**
     * Define a one-to-many relationship with the Order model.
     *
     * This method establishes that a Sale can have multiple orders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Define a one-to-many relationship with the SalesTarget model.
     *
     * This method establishes that a Sale can have multiple sales targets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function targets()
    {
        return $this->hasMany(SalesTarget::class);
    }
}
