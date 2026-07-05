<?php

namespace Webkul\WhatsApp\Http\Controllers\Admin;

use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Resources\ActivityResource;

class ActivityController extends Controller
{
    public function __construct(protected ActivityRepository $activityRepository) {}

    public function index(int $id)
    {
        $activities = \Webkul\WhatsApp\Models\WhatsAppConversation::query()
            ->findOrFail($id)
            ->activities()
            ->with(['user', 'files', 'participants.user', 'participants.person'])
            ->orderByDesc('created_at')
            ->get();

        return ActivityResource::collection($activities->values());
    }
}
