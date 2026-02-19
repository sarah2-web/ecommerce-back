<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
// use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class OrderController extends Controller
{
   // ======== GET ALL ORDERS ========
    public function index()
{
    $orders = Order::with(['items.product', 'user'])->get();
    return response()->json($orders, 200);
}

public function show($id)
{
    $order = Order::with(['items.product', 'user'])->find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    return response()->json($order, 200);
}

    // إنشاء طلب جديد (store)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.p_id' => 'required|exists:products,p_id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $total = 0;

        $order = Order::create([
            'user_id' => $request->user_id,
            'total_price' => 0,
            'status' => 'pending',
            'order_date' => Carbon::now()
        ]);

        foreach ($request->items as $item) {
            $product = Product::where('p_id', $item['p_id'])->first();
            $itemPrice = $product->price * $item['quantity'];
            $total += $itemPrice;

            OrderItem::create([
                'o_id' => $order->o_id,
                'p_id' => $product->p_id,
                'quantity' => $item['quantity'],
                'item_price' => $itemPrice
            ]);
        }

        $order->update(['total_price' => $total]);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items.product')
        ], 201);
    }

    // تحديث الطلب (update)
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
            'items' => 'sometimes|array|min:1',
            'items.*.p_id' => 'required_with:items|exists:products,p_id',
            'items.*.quantity' => 'required_with:items|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // تحديث الحالة
        if ($request->has('status')) {
            $order->status = $request->status;
            $order->save();
        }

        // تحديث العناصر إذا تم تمريرها
        if ($request->has('items')) {
            // حذف العناصر القديمة
            $order->items()->delete();

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::where('p_id', $item['p_id'])->first();
                $itemPrice = $product->price * $item['quantity'];
                $total += $itemPrice;

                OrderItem::create([
                    'o_id' => $order->o_id,
                    'p_id' => $product->p_id,
                    'quantity' => $item['quantity'],
                    'item_price' => $itemPrice
                ]);
            }

            $order->update(['total_price' => $total]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('items.product')
        ], 200);
    }

    // حذف الطلب (destroy)
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // حذف العناصر أولاً
        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }

    // عرض طلبات مستخدم معين
    public function userOrders($userId)
    {
        $orders = Order::where('user_id', $userId)
            ->with('items.product')
            ->get();

        return response()->json($orders, 200);
    }

    // ================== إضافة منتج للكارت ==================
    public function addToCart(Request $request)
    {
        $user = $request->user();

if (!$user) {
    return response()->json([
        'message' => 'يجب تسجيل الدخول أولاً'
    ], 401);
}
        // $user = $request->user();

        // $request->validate([
        //     'product_id' => 'required|exists:products,p_id',
        // ]);

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'message' => 'المنتج غير موجود',
                'items' => [],
                'total_price' => 0
            ], 404);
        }

        $order = Order::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'pending'
            ],
            [
                'total_price' => 0,
                'order_date' => now()
            ]
        );

        if (!$order) {
            return response()->json([
                'message' => 'فشل في إنشاء الطلب',
                'items' => [],
                'total_price' => 0
            ], 500);
        }

        $item = OrderItem::where('o_id', $order->o_id)
            ->where('p_id', $product->p_id)
            ->first();

        if ($item) {
            $item->quantity += 1;
            $item->item_price = $product->price;
            $item->save();
        } else {
            OrderItem::create([
                'o_id' => $order->o_id,
                'p_id' => $product->p_id,
                'quantity' => 1,
                'item_price' => $product->price
            ]);
        }

        $total = OrderItem::where('o_id', $order->o_id)
            ->sum(DB::raw('quantity * item_price'));
        $order->update(['total_price' => $total]);

        return response()->json([
            'message' => 'تمت إضافة المنتج للكارت',
            'cart_total' => $total
        ]);
    }

    // ================== جلب الكارت الحالي ==================
    public function getCart(Request $request)
{
    $user = $request->user();

    // لو مفيش مستخدم مسجل دخول
    if (!$user) {
        return response()->json([
            'items' => [],
            'total_price' => 0,
            'message' => 'User not logged in'
        ]);
    }

    $order = Order::with('items.product')
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if (!$order) {
        return response()->json([
            'items' => [],
            'total_price' => 0
        ]);
    }

    return response()->json([
        'items' => $order->items,
        'total_price' => $order->total_price
    ]);
}

    // ================== إزالة منتج ==================
    public function removeCartItem(Request $request, $itemId)
    {
        $user = $request->user();

        $item = OrderItem::with('order')->find($itemId);

        if (!$item) {
            return response()->json(['message' => 'المنتج غير موجود'], 404);
        }

        if (!$item->order) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }

        if ($item->order->user_id !== $user->id || $item->order->status !== 'pending') {
            return response()->json(['message' => 'غير مسموح'], 403);
        }

        $o_id = $item->o_id;
        $item->delete();

        $total = OrderItem::where('o_id', $o_id)
            ->sum(DB::raw('quantity * item_price'));

        $order = Order::find($o_id);
        if ($order) {
            $order->update(['total_price' => $total]);
        }

        return response()->json([
            'message' => 'تم حذف المنتج',
            'cart_total' => $total
        ]);
    }

    // ================== Checkout ==================
    public function checkout(Request $request)
    {
        $user = $request->user();

        $order = Order::with('items')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'لا يوجد طلب نشط'], 400);
        }

        if (!$order->items || $order->items->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة'], 400);
        }

        $order->update([
            'status' => 'paid',
            'order_date' => now()
        ]);

        return response()->json([
            'message' => 'تم إتمام الطلب بنجاح',
            'order_id' => $order->o_id
        ]);
    }


}
