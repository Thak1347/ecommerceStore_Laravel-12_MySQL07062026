<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest; 
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search by name or SKU
        if($request->has('search')){
            $search = $request->search;

            $query->where(function ($q) use ($search){
                $q->where('name','like',"%{$search}%")->orWhere("sku","like","%{$search}%");
            }
            );
        }

        // Filter by category
        if($request->has("category_id")){
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if($request->has('min_price')){
            $query->where('price', '>=', $request->min_price);

        }
        if($request->has('max_price')){
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock status
        if($request->has('in_stock')){
            if($request->boolean('in_stock')){
                $query->where('stock_qty', '>', 0);

            }else{
                $query->where('stock_qty',0);
            }
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        // Add image URL
        $products->getCollection()->transform(function ($product) {
            $product->image_url = $product->getImageUrlAttribute();
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        if($request->hasFile('image')){
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product = Product::create($data);
        $product->load('category');
        $product->image_url = $product->getImageUrlAttribute();

        return response()->json(['message'=>'Product created successfully', 'product'=> $product], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);
        $product->image_url = $product->getImageUrlAttribute();

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product->update($data);
        $product->load('category');
        $product->image_url = $product->getImageUrlAttribute();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product has orders
        if ($product->orderItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete product with associated orders'
            ], 422);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    // 
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock_qty' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($id);
        $product->stock_qty = $request->stock_qty;
        $product->save();

        return response()->json([
            'message' => 'Stock updated successfully',
            'product' => $product
        ]);
    }
}
