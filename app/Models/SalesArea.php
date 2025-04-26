<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesArea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
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
     * Define a one-to-one relationship with the Sales model.
     *
     * This method establishes that a SalesArea has one sales.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sales()
    {
        return $this->hasOne(Sales::class);
    }
}
