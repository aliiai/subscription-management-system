<?php

namespace App\Http\Resources\Api\V1;

use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin JournalLine
 */
class JournalLineResource extends JsonResource
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
            'account_id' => $this->account_id,
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'account' => new AccountResource($this->whenLoaded('account')),
        ];
    }
}
