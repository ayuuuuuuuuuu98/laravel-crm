<?php

namespace Webkul\ScalvionFoundation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EntityNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $actionUrl = null,
        protected array $meta = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'      => $this->title,
            'body'       => $this->body,
            'action_url' => $this->actionUrl,
            'meta'       => $this->meta,
        ];
    }
}
