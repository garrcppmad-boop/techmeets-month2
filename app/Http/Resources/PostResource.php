<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'author' => $this->whenLoaded('user', fn () => $this->user->name),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
