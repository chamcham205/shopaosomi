@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-1">Báo cáo doanh thu</h2>

    <nav class="nav nav-pills my-3" aria-label="Báo cáo">
        <a class="nav-link active" aria-current="page" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
        <a class="nav-link" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </nav>

    <p class="text-muted">Doanh thu tính theo ngày tạo đơn, chỉ gồm đơn đã thanh toán, chưa hoàn tiền và không bị hủy hoặc hoàn hàng.</p>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm border-0 rounded-3">
                <span class="text-muted small fw-semibold">Tổng số đơn hàng</span>
                <h3 class="mb-0 mt-1 fw-bold">{{ number_format($totalOrders) }}</h3>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm border-0 rounded-3">
                <span class="text-muted small fw-semibold">Tổng số khách hàng</span>
                <h3 class="mb-0 mt-1 fw-bold">{{ number_format($totalCustomers) }}</h3>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm border-0 rounded-3">
                <span class="text-muted small fw-semibold">Tổng doanh thu (gồm phí vận chuyển)</span>
                <h3 class="mb-0 mt-1 text-success fw-bold">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h3>
            </div>
        </div>
    </div>

    {{-- Doanh thu theo danh mục --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white py-3">
            <strong class="fs-6">Doanh thu theo danh mục</strong>
            <div class="small text-muted">Tính theo giá sản phẩm khi đặt hàng, không gồm phí vận chuyển.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Danh mục</th>
                        <th class="text-end">Số lượng bán</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoryRevenue as $revenue)
                        <tr>
                            <td class="fw-medium">{{ $revenue->category_name ?? ('Danh mục #'.$revenue->category_id) }}</td>
                            <td class="text-end">{{ number_format($revenue->total_qty) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach([
        ['Doanh thu theo ngày', 'Ngày', 'date', $revenueByDate, 'd/m/Y'],
        ['Doanh thu theo tháng', 'Tháng', 'month', $revenueByMonth, 'm/Y'],
        ['Doanh thu theo năm', 'Năm', 'year', $revenueByYear, null],
    ] as [$title, $label, $field, $rows, $format])
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 font-weight-bold">
                <strong>{{ $title }}</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ $label }}</th>
                            <th class="text-end">Số đơn đã thanh toán</th>
                            <th class="text-end">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $revenue)
                            <tr>
                                <td class="fw-medium">
                                    {{ $format ? \Carbon\Carbon::parse($revenue->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $revenue->{$field} }}
                                </td>
                                <td class="text-end">{{ number_format($revenue->order_count) }}</td>
                                <td class="text-end fw-semibold">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
