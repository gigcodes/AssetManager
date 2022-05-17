<?php

namespace Gigcodes\AssetManager\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->name,
            'item_id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'edit_url' => route(config('asset-manager.route.name') . '.container.edit', ['collection' => $this->name]),
            'browse_url' => route(config('asset-manager.route.name') . '.container', ['collection' => $this->name]),
        ];
    }
}
