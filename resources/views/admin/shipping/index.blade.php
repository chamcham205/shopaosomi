@extends('layouts.admin')
@section('title','Cấu hình vận chuyển')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Cấu hình vận chuyển</h1>
        <p class="text-muted mb-0">Hệ thống đang tự động hóa 100% qua API Giao Hàng Nhanh (GHN Express)</p>
    </div>
    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
        <i class="fa-solid fa-circle-check me-1"></i>GHN API Đang Hoạt Động (Toàn Quốc)
    </span>
</div>

<div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center gap-3">
    <div class="rounded-circle bg-white p-2 text-primary shadow-sm d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
        <i class="fa-solid fa-truck-fast fs-4"></i>
    </div>
    <div>
        <strong class="text-dark">Đã kích hoạt tự động tính phí vận chuyển GHN Toàn quốc 63 tỉnh/thành!</strong>
        <div class="small text-secondary mt-1">
            Cước phí được tính toán tự động qua API GHN thời gian thực và đồng bộ vận đơn sang <code>5sao.ghn.dev</code> (Shop ID: <code>216757</code>).
            Cấu hình khu vực thủ công cũ dưới đây đã ngưng áp dụng trên giao diện mua hàng của khách.
        </div>
    </div>
</div>

<div class="card card-body mb-4 shadow-sm border-0 rounded-3">
    <h2 class="h5">Thêm khu vực thủ công (Tùy chọn lưu trữ)</h2>
    <form method="POST" action="{{ route('admin.shipping.store') }}" class="row g-2">
        @csrf
        @foreach([['name','Khu vực'],['code','Mã'],['rate_name','Dịch vụ'],['fee','Phí'],['free_shipping_threshold','Ngưỡng freeship']] as [$name,$label])
            <div class="col-md">
                <input class="form-control" name="{{ $name }}" placeholder="{{ $label }}">
            </div>
        @endforeach
        <input type="hidden" name="status" value="active">
        <div class="col-auto">
            <button class="btn btn-primary">Thêm</button>
        </div>
    </form>
</div>

@foreach($zones as $zone)
    @php($rate=$zone->rates->first())
    <form method="POST" action="{{ route('admin.shipping.update',$zone) }}" class="card card-body mb-3 shadow-sm border-0 rounded-3">
        <div class="row g-2">
            @csrf
            @method('PUT')
            <div class="col"><input class="form-control" name="name" value="{{ $zone->name }}"></div>
            <div class="col"><input class="form-control" name="code" value="{{ $zone->code }}"></div>
            <div class="col"><input class="form-control" name="rate_name" value="{{ $rate?->name }}"></div>
            <div class="col"><input class="form-control" name="fee" value="{{ $rate?->fee }}"></div>
            <div class="col"><input class="form-control" name="free_shipping_threshold" value="{{ $rate?->free_shipping_threshold }}"></div>
            <div class="col">
                <select class="form-select" name="status">
                    <option value="active">Active</option>
                    <option value="inactive" @selected($zone->status==='inactive')>Inactive</option>
                </select>
                <input type="hidden" name="rate_status" value="{{ $rate?->status ?? 'active' }}">
            </div>
            <div class="col-auto"><button class="btn btn-outline-primary">Lưu</button></div>
        </div>
    </form>
@endforeach
@endsection

