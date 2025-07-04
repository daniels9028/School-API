<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function index()
    {
        return Tag::latest()->get();
    }

    public function store(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag->fresh();
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}
