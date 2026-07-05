<?php

namespace Webkul\ScalvionFoundation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ScalvionFoundation\Support\EntityResolver;
use Webkul\ScalvionFoundation\Support\TimelineAggregator;

class TimelineController extends Controller
{
    public function __construct(
        protected EntityResolver $entityResolver,
        protected TimelineAggregator $timelineAggregator,
    ) {}

    public function index(Request $request, string $entityType, int $entityId): JsonResponse
    {
        $entity = $this->entityResolver->find($entityType, $entityId);
        $limit = min((int) $request->input('limit', 50), 100);

        return response()->json([
            'data' => $this->timelineAggregator->forEntity($entity, $limit),
        ]);
    }
}
