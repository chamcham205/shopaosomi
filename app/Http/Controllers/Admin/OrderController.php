<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const TABS = [
        'all' => ['label' => 'Tất cả', 'color' => 'blue'],
        'pending' => ['label' => 'Chờ xử lý', 'color' => 'slate'],
        'ready' => ['label' => 'Chờ lấy hàng', 'color' => 'cyan'],
        'picking' => ['label' => 'Đang lấy hàng', 'color' => 'cyan'],
        'delivering' => ['label' => 'Đang giao', 'color' => 'amber'],
        'delivered' => ['label' => 'Thành công', 'color' => 'green'],
        'return' => ['label' => 'Hoàn hàng', 'color' => 'orange'],
        'cancelled' => ['label' => 'Đã hủy', 'color' => 'red'],
    ];

    /**
     * Hiển thị danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $paymentLabels = [
            'pending' => 'Chờ thanh toán',
            'initiated' => 'Đang chờ MoMo',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refund_pending' => 'Chờ hoàn tiền',
            'refunded' => 'Đã hoàn tiền',
        ];

        $shippingLabels = [
            'pending' => 'Chờ tạo vận đơn',
            'not_shipped' => 'Chưa giao hàng',
            'processing' => 'Đang tạo vận đơn',
            'ready_to_pick' => 'Chờ lấy hàng',
            'picking' => 'Đang lấy hàng',
            'picked' => 'Đã lấy hàng',
            'storing' => 'Đang lưu kho',
            'transporting' => 'Đang trung chuyển',
            'sorting' => 'Đang phân loại',
            'delivering' => 'Đang giao hàng',
            'delivered' => 'Giao hàng thành công',
            'return' => 'Chờ hoàn hàng',
            'returning' => 'Đang hoàn hàng',
            'returned' => 'Đã hoàn hàng',
            'return_transporting' => 'Đang chuyển hoàn',
            'return_sorting' => 'Đang phân loại hoàn',
            'cancelled' => 'Đã hủy',
        ];

        // Hỗ trợ tham số tìm kiếm q hoặc search
        if ($request->filled('q') && !$request->filled('search')) {
            $request->merge(['search' => $request->q]);
        }
        if ($request->filled('date') && !$request->filled('date_from')) {
            $request->merge(['date_from' => $request->date, 'date_to' => $request->date]);
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'paid_momo', 'cod_ordered', 'cod_paid', 'confirmed', 'packing', 'shipping', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(array_keys($paymentLabels))],
            'shipping_status' => ['nullable', Rule::in(array_keys($shippingLabels))],
            'gateway' => ['nullable', Rule::in(['cod', 'momo', 'unknown'])],
            'tab' => ['nullable', Rule::in(array_keys(self::TABS))],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 25, 50, 100])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'amount_desc', 'amount_asc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'date_to.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            '*.date_format' => 'Ngày lọc không hợp lệ.',
            '*.in' => 'Giá trị bộ lọc không hợp lệ.',
        ]);

        $paymentId = DB::table('payment_transactions')->select('id')->whereColumn('order_id', 'orders.id')
            ->orderByRaw("CASE WHEN status IN ('paid', 'refund_pending', 'refunded') THEN 0 ELSE 1 END")
            ->orderByDesc('id')->limit(1);

        $source = DB::table('orders')->leftJoin('payment_transactions as payment', function ($join) use ($paymentId) {
            $join->on('payment.order_id', '=', 'orders.id')->where('payment.id', '=', $paymentId);
        })->select('orders.*')
            ->selectRaw("COALESCE(payment.gateway, CASE WHEN orders.status IN ('cod_ordered', 'cod_paid') THEN 'cod' WHEN orders.status IN ('paid', 'paid_momo') THEN 'momo' ELSE 'unknown' END) as gateway")
            ->selectRaw("COALESCE(payment.status, CASE WHEN orders.status = 'cod_ordered' THEN 'pending' WHEN orders.status IN ('cod_paid', 'paid_momo', 'completed') THEN 'paid' ELSE orders.status END) as payment_status");

        $query = Order::query()->fromSub($source, 'orders');

        foreach (['status', 'payment_status', 'gateway'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $filters[$field]);
            }
        }

        if ($request->filled('search')) {
            $search = trim($filters['search']);
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('order_code', 'like', '%'.$search.'%')
                    ->orWhere('recipient_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('ghn_order_code', 'like', '%'.$search.'%')
                    ->orWhereHas('items', function ($items) use ($search) {
                        $items->where('product_name', 'like', '%'.$search.'%');
                    });
                if (preg_match('/^(?:#|DH)?0*(\d+)$/i', $search, $matches)) {
                    $query->orWhere('orders.id', $matches[1]);
                }
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay());
        }

        // Tính số lượng đơn cho từng Tab theo đúng logic nghiệp vụ
        $tabs = collect(self::TABS)->map(function ($tab, $key) use ($query) {
            $countQuery = clone $query;
            if ($key !== 'all') {
                self::applyWorkflowTabFilter($countQuery, $key);
            }
            $tab['count'] = $countQuery->count();
            return $tab;
        });

        $activeTab = $filters['tab'] ?? 'all';
        if ($activeTab !== 'all') {
            self::applyWorkflowTabFilter($query, $activeTab);
        }
        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $filters['shipping_status']);
        }

        [$column, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['created_at', 'asc'],
            'amount_desc' => ['total', 'desc'],
            'amount_asc' => ['total', 'asc'],
            default => ['created_at', 'desc'],
        };

        $perPage = (int) ($filters['per_page'] ?? 25);
        $orders = $query->with(['user', 'items.product'])
            ->orderBy($column, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.orders.index', compact(
            'orders', 'filters', 'tabs', 'activeTab', 'paymentLabels', 'shippingLabels'
        ));
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = ($id instanceof Order) ? $id : Order::with(['user', 'items.product', 'statusLogs', 'paymentTransactions' => function ($query) {
            $query->latest();
        }])->findOrFail($id);

        if (!$order->relationLoaded('paymentTransactions')) {
            $order->load(['paymentTransactions' => fn($q) => $q->latest()]);
        }
        if (!$order->relationLoaded('statusLogs')) {
            $order->load(['statusLogs']);
        }

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng (duy trì tương thích Phase 6)
     */
    public function updateStatus(Request $r, Order $order, OrderService $service)
    {
        $d = $r->validate([
            'status' => 'required|in:confirmed,packing,shipping,completed,cancelled',
            'note'   => 'nullable|string|max:500',
        ]);

        $service->transitionByAdmin($r->user(), $order, $d['status'], $d['note'] ?? null);
        return back()->with('success', 'Đã cập nhật trạng thái.');
    }

    public static function applyWorkflowTabFilter($query, string $tab): void
    {
        switch ($tab) {
            case 'cancelled':
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail'])
                        ->orWhere('payment_status', 'cancelled');
                });
                break;

            case 'return':
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail'])
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->where(function ($sub) {
                    $sub->where('orders.status', 'return')
                        ->orWhereIn('orders.shipping_status', ['return', 'returning', 'returned', 'return_transporting', 'return_sorting']);
                });
                break;

            case 'delivered':
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting'])
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->where(function ($sub) {
                    $sub->where('orders.shipping_status', 'delivered')
                        ->orWhere('orders.status', 'completed');
                });
                break;

            case 'delivering':
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting', 'delivered'])
                        ->orWhere('orders.status', 'completed')
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->whereIn('orders.shipping_status', ['delivering', 'picked', 'storing', 'transporting', 'sorting', 'shipping']);
                break;

            case 'picking':
                // Đang lấy hàng:
                // - thanh toán bằng MoMo thành công và có mã vận đơn
                // - thanh toán COD (trực tiếp về đang lấy hàng)
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting', 'delivered', 'delivering', 'picked', 'storing', 'transporting', 'sorting', 'shipping'])
                        ->orWhere('orders.status', 'completed')
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->where(function ($sub) {
                    // Nhánh COD:
                    $sub->where(function ($cod) {
                        $cod->where('gateway', 'cod')->orWhere('payment_method', 'cod');
                    })
                    // Nhánh MoMo thành công và có mã vận đơn:
                    ->orWhere(function ($momo) {
                        $momo->where(function ($w) {
                            $w->where('gateway', 'momo')->orWhere('payment_method', 'momo');
                        })
                        ->where(function ($w) {
                            $w->whereIn('payment_status', ['paid', 'cod_paid'])
                              ->orWhereIn('orders.status', ['paid', 'paid_momo']);
                        })
                        ->whereNotNull('orders.ghn_order_code')
                        ->where('orders.ghn_order_code', '!=', '');
                    })
                    // Hoặc bất kỳ đơn nào có mã vận đơn sẵn sàng lấy hàng:
                    ->orWhere(function ($w) {
                        $w->whereNotNull('orders.ghn_order_code')
                          ->where('orders.ghn_order_code', '!=', '')
                          ->whereIn('orders.shipping_status', ['ready_to_pick', 'picking']);
                    });
                });
                break;

            case 'ready':
                // Chờ lấy hàng: thanh toán bằng MoMo thành công nhưng chưa có mã vận đơn
                // Hoặc đơn ready_to_pick nhưng chưa có mã vận đơn
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting', 'delivered', 'delivering', 'picked', 'storing', 'transporting', 'sorting', 'shipping'])
                        ->orWhere('orders.status', 'completed')
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->where(function ($sub) {
                    $sub->where(function ($momo) {
                        $momo->where('gateway', 'momo')->orWhere('payment_method', 'momo')
                             ->whereIn('payment_status', ['paid', 'cod_paid'])
                             ->orWhereIn('orders.status', ['paid', 'paid_momo']);
                    })
                    ->orWhere('orders.shipping_status', 'ready_to_pick');
                })
                ->where(function ($sub) {
                    $sub->whereNull('orders.ghn_order_code')->orWhere('orders.ghn_order_code', '');
                });
                break;

            case 'pending':
                // Chờ xử lý: thanh toán bằng MoMo thất bại / chờ MoMo
                $query->where(function ($sub) {
                    $sub->where('orders.status', 'cancelled')
                        ->orWhereIn('orders.shipping_status', ['cancelled', 'cancel', 'delivery_fail', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting', 'delivered', 'delivering', 'picked', 'storing', 'transporting', 'sorting', 'shipping'])
                        ->orWhere('orders.status', 'completed')
                        ->orWhere('payment_status', 'cancelled');
                }, null, null, 'and not')
                ->where(function ($sub) {
                    $sub->where(function ($momo) {
                        $momo->where(function ($w) {
                            $w->where('gateway', 'momo')->orWhere('payment_method', 'momo');
                        })
                        ->where(function ($w) {
                            $w->whereNotIn('payment_status', ['paid', 'cod_paid'])
                              ->whereNotIn('orders.status', ['paid', 'paid_momo']);
                        });
                    })
                    ->orWhere('orders.status', 'pending');
                })
                ->where(function ($sub) {
                    $sub->where('gateway', '!=', 'cod')->where('payment_method', '!=', 'cod');
                });
                break;
        }
    }
}
