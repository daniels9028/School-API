<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $this->categoryService->index()
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->store([
            'name' => $request['name'],
            'slug' => Str::slug($request['name'], '-')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $updatedCategory = $this->categoryService->update($category, [
            'name' => $request['name'],
            'slug' => Str::slug($request['name'], '-')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $updatedCategory
        ]);
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
            'data' => null
        ]);
    }
}
