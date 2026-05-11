<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'state',
        'country_id',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function countryRecord(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }
}
