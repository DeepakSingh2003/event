<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecommendationController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendationService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $events = $this->recommendationService->forUser($request->user());

        return EventResource::collection($events);
    }
}
