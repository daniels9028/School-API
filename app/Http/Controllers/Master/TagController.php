<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(private TagService $tagService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Tags retrieved successfully',
            'data' => $this->tagService->index()
        ]);
    }

    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'data' => $tag
        ], 201);
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $udpatedTag = $this->tagService->update($tag, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'data' => $udpatedTag
        ]);
    }

    public function destroy(Tag $tag)
    {
        $this->tagService->delete($tag);

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
            'data' => null
        ]);
    }
}
