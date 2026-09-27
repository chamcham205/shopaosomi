@extends('layouts.admin')

@section('title', 'Biểu đồ báo cáo doanh thu')

@section('content')
<style>
    .chart-wrap { min-height: 360px; position: relative; }
    .chart-wrap canvas { width: 100% !important; height: 360px !important; }
</style>

<div class="container-fluid">
    <h2 class="h3 mb-1">Biểu đồ báo cáo doanh thu</h2>

    <nav class="nav nav-pills my-3" aria-label="Báo cáo">
        <a class="nav-link" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
        <a class="nav-link active" aria-current="page" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </nav>

    <p class="text-muted">Chỉ gồm đơn đã thanh toán, chưa hoàn tiền và không bị hủy hoặc hoàn hàng. Doanh thu tính theo ngày tạo đơn; số liệu theo danh mục không gồm phí vận chuyển.</p>

    <div id="report-chart-error" class="alert alert-warning d-none" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>Không tải được thư viện biểu đồ. Bạn có thể xem số liệu tại trang <a href="{{ route('admin.reports.index') }}">Bảng số liệu</a>.
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-0 rounded-3">
                <div class="card-header bg-white py-3 fw-bold">Doanh thu theo danh mục</div>
                <div class="card-body chart-wrap"><canvas id="categoryRevenueChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-0 rounded-3">
                <div class="card-header bg-white py-3 fw-bold">Doanh thu theo ngày (30 ngày)</div>
                <div class="card-body chart-wrap"><canvas id="revenueByDateChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-0 rounded-3">
                <div class="card-header bg-white py-3 fw-bold">Doanh thu theo tháng (12 tháng)</div>
                <div class="card-body chart-wrap"><canvas id="revenueByMonthChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-0 rounded-3">
                <div class="card-header bg-white py-3 fw-bold">Doanh thu theo năm</div>
                <div class="card-body chart-wrap"><canvas id="revenueByYearChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 fw-bold">Doanh thu theo phương thức thanh toán</div>
                <div class="card-body chart-wrap"><canvas id="revenueByPaymentMethodChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div id="report-chart-data" hidden data-chart-data="{{ json_encode([
    'catLabels' => $catLabels ?? [],
    'catRevenue' => $catRevenue ?? [],
    'revDateLabels' => $revDateLabels ?? [],
    'revDateData' => $revDateData ?? [],
    'revMonthLabels' => $revMonthLabels ?? [],
    'revMonthData' => $revMonthData ?? [],
    'revYearLabels' => $revYearLabels ?? [],
    'revYearData' => $revYearData ?? [],
    'paymentMethodLabels' => $paymentMethodLabels ?? [],
    'paymentMethodRevenue' => $paymentMethodRevenue ?? [],
]) }}"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        document.getElementById('report-chart-error').classList.remove('d-none');
        return;
    }

    const dataEl = document.getElementById('report-chart-data');
    if (!dataEl || !dataEl.dataset.chartData) return;

    const reportData = JSON.parse(dataEl.dataset.chartData);
    const catLabels = reportData.catLabels;
    const catRevenue = reportData.catRevenue.map(Number);
    const revDateLabels = reportData.revDateLabels;
    const revDateData = reportData.revDateData.map(Number);
    const revMonthLabels = reportData.revMonthLabels;
    const revMonthData = reportData.revMonthData.map(Number);
    const revYearLabels = reportData.revYearLabels;
    const revYearData = reportData.revYearData.map(Number);
    const payLabels = reportData.paymentMethodLabels;
    const payRevenue = reportData.paymentMethodRevenue.map(Number);

    const formatVND = (v) => new Intl.NumberFormat('vi-VN').format(v) + ' ₫';

    const mk = (el, type, labels, data, label, bgColor = '#c8a96e') => {
        if (!el) return null;
        return new Chart(el, {
            type,
            data: {
                labels,
                datasets: [{
                    label,
                    data,
                    backgroundColor: type === 'line' ? 'rgba(200, 169, 110, 0.15)' : (type === 'pie' ? ['#a50064', '#1a1a2e'] : '#c8a96e'),
                    borderColor: '#c8a96e',
                    borderWidth: 1.5,
                    fill: type === 'line',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => formatVND(val)
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label || ''}: ${formatVND(ctx.raw)}`
                        }
                    }
                }
            }
        });
    };

    mk(document.getElementById('categoryRevenueChart'), 'bar', catLabels, catRevenue, 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByDateChart'), 'line', revDateLabels, revDateData, 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByMonthChart'), 'bar', revMonthLabels, revMonthData, 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByYearChart'), 'bar', revYearLabels, revYearData, 'Doanh thu (VNĐ)');

    const pieEl = document.getElementById('revenueByPaymentMethodChart');
    if (pieEl) {
        new Chart(pieEl, {
            type: 'pie',
            data: {
                labels: payLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: payRevenue,
                    backgroundColor: ['#a50064', '#1a1a2e', '#64748b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${formatVND(ctx.raw)}`
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
