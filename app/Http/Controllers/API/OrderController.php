<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class OrderController extends Controller
{

        // $user = $request->user();

        // if($user->isAdmin()){
        //     $query = Order::where('customer');
        // }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();

        if($user->isAdmin()){
            $query = Order::with('customer');
        }else{
            $query = Order::where('customer_id', $user->id)->where('customer');
        }

        // Filter by status
        if ($request->has('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Filter by customer_id
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }


        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash_on_delivery,card,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock_qty < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;
                
                $orderItems[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'qty' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $itemSubtotal
                ];
            }

            $tax = $subtotal * 0.1; // 10% tax
            $shippingFee = $request->shipping_fee ?? 0;
            $total = $subtotal + $tax + $shippingFee;

            $order = Order::create([
                'customer_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'pending',
                'order_status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Decrease stock
                $item['product']->decreaseStock($item['qty']);
            }

            DB::commit();

            $order->load('items.product', 'customer');

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = request()->user();
        $order = Order::with('items.product', 'customer')->findOrFail($id);

        if (!$user->isAdmin() && $order->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, String $id)
    {
        $user = request()->user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_status' => 'sometimes|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|string|in:pending,paid,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::findOrFail($id);
        
        if ($request->has('order_status')) {
            $order->order_status = $request->order_status;
        }
        
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        $order->save();

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }

    public function myOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('customer_id', $user->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
