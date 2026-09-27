<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminMetricsService;

/**
 * DashboardController (Admin)
 *
 * Admin Dashboard placeholder cho Phase 1.
 * Mục tiêu: test role + middleware + redirect.
 * Sẽ được hoàn thiện ở Phase 6 với Cards, Charts, Reports...
 */
class DashboardController extends Controller
{
    public function index(AdminMetricsService $metrics)
    {
        $overview=$metrics->overview();$recentOrders=Order::with('user')->latest()->limit(8)->get();$topProducts=$metrics->topProducts(5);$revenueChart=$metrics->revenue7Days();
        return view('admin.dashboard.index',compact('overview','recentOrders','topProducts','revenueChart'));
    }
}
