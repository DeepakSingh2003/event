<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'category_id',
        'slug',
        'poster_image',
        'banner_image',
        'language',
        'status',
        'publication_status',
        'meta_title',
        'meta_description',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function shows(): HasMany
    {
        return $this->hasMany(Show::class)->latest('show_date')->latest('show_time');
    }

    public function primaryShow(): HasOne
    {
        return $this->hasOne(Show::class)->orderBy('show_date')->orderBy('show_time');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(EventGallery::class)->orderBy('sort_order');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(EventTimeline::class)->orderBy('sort_order');
    }
}
