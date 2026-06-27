<?php

namespace App\Http\Resources\Api\V1;

use App\Models\TenantNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantNotification
 */
class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'color' => $this->color,
            'url' => $this->url,
            'is_unread' => $this->isUnread(),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
