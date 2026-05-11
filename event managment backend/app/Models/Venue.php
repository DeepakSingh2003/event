<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'city_id',
        'slug',
        'address',
        'total_seats',
        'latitude',
        'longitude',
        'map_url',
        'layout_image',
        'layout_label',
        'layout_label_position',
        'row_count',
        'column_count',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function cityRecord(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function shows(): HasMany
    {
        return $this->hasMany(Show::class);
    }
}
