<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function stats()
    {
        $totalOrders = Order::count();
        $totalRevenue  = Order::sum('total');
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentOrders = Order::with('customer')->orderBy('created_at','desc')->limit(5)->get();

        $lowStockProducts = Product::where('stock_qty', '<', 10)->where('active', true)->limit(5)->get();

        $monthlyRevenue = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue')
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $topProducts = OrderItem::select(
            'product_id',
            DB::raw('SUM(qty) as total_sold')
        )
        ->with('product')
        ->groupBy('product_id')
        ->orderBy('total_sold', 'desc')
        ->limit(5)
        ->get();

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProducts,
            'monthly_revenue' => $monthlyRevenue,
            'top_products' => $topProducts,
        ]);
    }
}
