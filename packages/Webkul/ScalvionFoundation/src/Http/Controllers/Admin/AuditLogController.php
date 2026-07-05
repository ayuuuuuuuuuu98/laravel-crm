<?php

namespace Webkul\ScalvionFoundation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ScalvionFoundation\Models\AuditLog;
use Webkul\ScalvionFoundation\Support\EntityResolver;
use Webkul\ScalvionFoundation\Support\TimelineAggregator;

class AuditLogController extends Controller
{
    public function __construct(
        protected EntityResolver $entityResolver,
        protected TimelineAggregator $timelineAggregator,
    ) {}

    public function index(): View
    {
        $logs = AuditLog::query()->with('user')->latest()->paginate(30);

        return view('scalvion-foundation::admin.audit-logs.index', compact('logs'));
    }

    public function entity(string $entityType, int $entityId): JsonResponse
    {
        $entity = $this->entityResolver->find($entityType, $entityId);

        return response()->json([
            'data' => $this->timelineAggregator->auditsForEntity($entity, 50),
        ]);
    }
}
