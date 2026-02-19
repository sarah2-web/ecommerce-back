<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // ======== GET ALL PRODUCTS ========
    public function index()
    {
        $products = Product::all();

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'p_id'=> $product->p_id,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'category' => $product->category,
                'stock'=> $product->stock,
                'size' => $product->size,
                'is_new' => $product->is_new,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
            ];
        });

        return response()->json($products);
    }

    // ======== CREATE NEW PRODUCT ========
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock'=> 'nullable|integer|min:0',
            'is_new' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->only(['name','description','price','category','size','stock','is_new']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product = Product::create($data);

        $productData = $product->toArray();
        $productData['image'] = $product->image ? asset('storage/' . $product->image) : null;

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $productData
        ], 201);
    }

    // ======== GET SINGLE PRODUCT ========
    public function show($id)
    {
        $product = Product::findOrFail($id);

        $productData = $product->toArray();
        $productData['image'] = $product->image ? asset('storage/' . $product->image) : null;

        return response()->json($productData);
    }

    // ======== UPDATE PRODUCT ========
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // ✅ أضفنا 'is_new' هنا
        $data = $request->only(['name','description','price','category','size','stock','is_new']);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products','public');
            $data['image'] = $path;
        }

        $product->update($data);
        $product->refresh();

        $productData = $product->toArray();
        $productData['image'] = $product->image ? asset('storage/' . $product->image) : null;

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $productData
        ]);
    }

    // ======== DELETE PRODUCT ========
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if($product->image){
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
