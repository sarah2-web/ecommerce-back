<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(OrderItem::all(), 200);
    }

    // POST /api/order-items
    public function store(Request $request)
    {
        // Debug لو احتجت
        // dd($request->all());

        $request->validate([
            'o_id'       => 'required|integer|exists:orders,id',
            'p_id'       => 'required|integer|exists:products,id',
            'quantity'  => 'required|integer|min:1',
            'item_price'=> 'required|numeric|min:0',
        ]);

        $item = OrderItem::create([
            'o_id'        => $request->o_id,
            'p_id'        => $request->p_id,
            'quantity'   => $request->quantity,
            'item_price' => $request->item_price,
        ]);

        return response()->json([
            'message' => 'تم إضافة العنصر بنجاح',
            'data' => $item
        ], 201);
    }

    // GET /api/order-items/{id}
    public function show($id)
    {
        $item = OrderItem::findOrFail($id);
        return response()->json($item, 200);
    }

    // PUT /api/order-items/{id}
    public function update(Request $request, $id)
    {
        $item = OrderItem::findOrFail($id);

        $request->validate([
            'quantity'   => 'sometimes|integer|min:1',
            'item_price'=> 'sometimes|numeric|min:0',
        ]);

        $item->update($request->only(['quantity', 'item_price']));

        return response()->json([
            'message' => 'تم التحديث بنجاح',
            'data' => $item
        ], 200);
    }

    // DELETE /api/order-items/{id}
    public function destroy($id)
    {
        $item = OrderItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'تم الحذف بنجاح'
        ], 200);
    }
}
