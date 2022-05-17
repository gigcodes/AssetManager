<?php

namespace Gigcodes\AssetManager\Http\Controllers\Traits;

use Gigcodes\AssetManager\Http\Resources\MediaCollectionResource;
use Gigcodes\AssetManager\Models\MediaCollection;
use Illuminate\Http\Request;

trait Collection
{
    public function getCollection($collection = 'main', $folder = '')
    {
        return response()->json([
            'columns' => ['title'],
            'items' => MediaCollectionResource::collection($this->collection::get())
        ]);
    }

    public function makeCollection(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:media_collections,name|string|bail|min:4|max:20|alpha_dash',
            'title' => 'required|string|bail|min:5|max:20'
        ]);
        $collection = $this->collection::create($request->only(['name', 'title']));

        return response([
            'message' => 'Collection created',
            'collection' => [
                'id' => $collection->id,
                'uuid' => $collection->uuid,
                'name' => $collection->name,
                'title' => $collection->title,
                'description' => $collection->description,
            ]
        ]);
    }

    public function deleteCollection(Request $request)
    {
        $collection = $this->collection::where('name', $request->get('collection'))->first();
        if ($collection) $collection->delete();
        else abort(404);
        return response([
            'message' => 'Collection deleted'
        ]);
    }
}