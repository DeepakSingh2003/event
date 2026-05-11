<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'language' => $this->language,
            'publication_status' => $this->publication_status,
            'is_featured' => $this->is_featured,
            'poster_image_url' => $this->imageUrl($this->poster_image),
            'banner_image_url' => $this->imageUrl($this->banner_image),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'category' => $this->eventCategory?->name ?? $this->category,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'gallery' => $this->galleryImages?->map(fn ($image) => [
                'id' => $image->id,
                'image_url' => asset('storage/'.$image->image_path),
                'caption' => $image->caption,
            ]),
            'timeline' => $this->timelines?->map(fn ($timeline) => [
                'title' => $timeline->title,
                'description' => $timeline->description,
                'starts_at' => optional($timeline->starts_at)?->toIso8601String(),
                'ends_at' => optional($timeline->ends_at)?->toIso8601String(),
            ]),
            'primary_listing' => $this->when(
                $this->relationLoaded('primaryShow') && $this->primaryShow,
                fn () => new ShowResource($this->primaryShow)
            ),
            'shows' => ShowResource::collection($this->whenLoaded('shows')),
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
