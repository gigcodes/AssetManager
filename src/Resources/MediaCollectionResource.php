<?php

namespace Gigcodes\AssetManager\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class MediaCollectionResource extends ResourceCollection
{
    private $data;

    public function __construct($resource, $data)
    {
        parent::__construct($resource);
        $this->data = $data;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request): array
    {
        return [
            'assets' => MediaIndexResource::collection($this->collection),
            ...$this->data
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        $jsonResponse["pagination"] = [
            "links" => $jsonResponse['links'],
            "meta" => $jsonResponse['meta'],
        ];
        unset($jsonResponse['links'],$jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}