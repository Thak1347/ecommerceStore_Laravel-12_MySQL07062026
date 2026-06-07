<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Search 
        if($request->has('search')){
            $query = $query->where('name','like','%'. $request->search .'%');

        }

        // Filter by active status
        if($request->has('active')){
            $query->where('active', $request->boolean('active'));
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sorOrder = $request->get('sort_order', 'desc');
        $query = $query->orderBy($sortField, $sorOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

        // Add image URL
        $categories->getCollection()->transform(function($category){
            $category->image_url = $category->getImageUrlAttribute();
            $category->products_count = $category->products()->count(); 
            return $category;

        });

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']);

        if($request->hasFile('image')){
            $path = $request->file('image')->store('categories', 'public');

            $data['image'] = $path;

        }

        $category = Category::create($data);
        $category->image_url = $category->getImageUrlAttribute();

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with('products')->findOrFail($id);
        $category->image_url = $category->getImageUrlAttribute();

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();

        if(isset($data['name'])){
            $data['slug'] = Str::slug($data['name']);
        }

        if($request->hasFile('image')){
            // Deleted old image
            if($category->image){
                Storage::disk('public')->delete($category->image);

            }
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }

        $category->update($data);
        $category->image_url = $category->getImageUrlAttribute();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        // check if category has products
        if($category->products()->count()>0){
            return response()->json([
                'message' => 'Cannot delete category with associated products'
            ],422);
        }

        // Delete image
        if($category->image){
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
