@extends('layouts.admin')
@section('title', 'Thêm Sản phẩm')
@section('page-title', 'Thêm Sản phẩm mới')

@push('styles')
<style>
    .variant-matrix th, .variant-matrix td { text-align:center; font-size:.82rem; padding:.4rem .5rem; }
    .variant-matrix input[type=number] { width:70px; text-align:center; }
    .color-swatch { width:18px;height:18px;border-radius:50%;border:2px solid #ddd;display:inline-block;vertical-align:middle; }
    .section-title { font-weight:700; font-size:.9rem; color:#374151; border-left:3px solid #c8a96e; padding-left:.75rem; margin-bottom:1rem; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-700">Thêm sản phẩm mới</h5>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-3">
    <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Vui lòng kiểm tra lại:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
@csrf
<div class="row g-4">

    {{-- LEFT: Thông tin cơ bản --}}
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-header">Thông tin cơ bản</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="VD: Sơ mi Oxford Premium Trắng">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                               value="{{ old('sku') }}" placeholder="VD: SM-OXF-001">
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug') }}" placeholder="Tự sinh nếu để trống">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả ngắn</label>
                        <input type="text" name="short_description" class="form-control"
                               value="{{ old('short_description') }}" maxlength="500">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Chất liệu</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material') }}"
                               placeholder="VD: Cotton 100%">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kiểu dáng (Fit)</label>
                        <input type="text" name="fit" class="form-control" value="{{ old('fit') }}"
                               placeholder="VD: Slim Fit">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thương hiệu</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand') }}"
                               placeholder="VD: ShirtStore">
                    </div>
                </div>
            </div>
        </div>

        {{-- Giá --}}
        <div class="card mb-4">
            <div class="card-header">Giá bán</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Giá gốc (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price') }}" min="0" step="1000">
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giá sale (VNĐ)</label>
                        <input type="number" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror"
                               value="{{ old('sale_price') }}" min="0" step="1000">
                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bắt đầu sale</label>
                        <input type="datetime-local" name="sale_start" class="form-control" value="{{ old('sale_start') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kết thúc sale</label>
                        <input type="datetime-local" name="sale_end" class="form-control" value="{{ old('sale_end') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Ảnh --}}
        <div class="card mb-4">
            <div class="card-header">Ảnh sản phẩm</div>
            <div class="card-body">
                <input type="file" name="images[]" class="form-control @error('images.*') is-invalid @enderror"
                       multiple accept="image/jpg,image/jpeg,image/png,image/webp">
                <div class="form-text">Cho phép: jpg, jpeg, png, webp. Tối đa 3MB mỗi ảnh. Ảnh đầu tiên sẽ là ảnh chính.</div>
                @error('images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Variants --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                Biến thể & Tồn kho
                <small class="text-muted fw-400">Chọn màu + size → bảng tồn kho tự sinh</small>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <p class="section-title">Màu sắc</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($colors as $color)
                            <div class="form-check d-flex align-items-center gap-2 me-3">
                                <input class="form-check-input color-check" type="checkbox"
                                       name="color_ids[]" value="{{ $color->id }}"
                                       id="color_{{ $color->id }}"
                                       {{ in_array($color->id, old('color_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex align-items-center gap-1" for="color_{{ $color->id }}">
                                    <span class="color-swatch" style="background:{{ $color->hex_code }}"></span>
                                    {{ $color->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="section-title">Kích cỡ</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($sizes as $size)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input size-check" type="checkbox"
                                       name="size_ids[]" value="{{ $size->id }}"
                                       id="size_{{ $size->id }}"
                                       {{ in_array($size->id, old('size_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="size_{{ $size->id }}">{{ $size->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Matrix tự sinh --}}
                <div id="variantMatrix" class="mt-3"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Trạng thái & Phân loại</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Chọn danh mục</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Đang bán</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured"
                           {{ old('featured') ? 'checked' : '' }}>
                    <label class="form-check-label" for="featured">
                        <i class="fa-solid fa-star text-warning me-1"></i>Sản phẩm nổi bật
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-accent w-100 py-2">
            <i class="fa-solid fa-floppy-disk me-2"></i>Tạo sản phẩm
        </button>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
// Dữ liệu màu và size từ PHP để sinh matrix
const colorData = @json($colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex_code]));
const sizeData  = @json($sizes->map(fn($s)  => ['id' => $s->id, 'name' => $s->name]));

function buildMatrix() {
    const selectedColors = [...document.querySelectorAll('.color-check:checked')].map(e => parseInt(e.value));
    const selectedSizes  = [...document.querySelectorAll('.size-check:checked')].map(e => parseInt(e.value));
    const container = document.getElementById('variantMatrix');

    if (!selectedColors.length || !selectedSizes.length) {
        container.innerHTML = '<p class="text-muted small">Vui lòng chọn ít nhất 1 màu và 1 kích cỡ để nhập tồn kho.</p>';
        return;
    }

    const cols = sizeData.filter(s => selectedSizes.includes(s.id));
    const rows = colorData.filter(c => selectedColors.includes(c.id));

    let html = `<div class="table-responsive"><table class="table table-bordered variant-matrix">
    <thead class="table-light"><tr><th>Màu \\ Size</th>`;
    cols.forEach(s => html += `<th>${s.name}</th>`);
    html += `</tr></thead><tbody>`;

    rows.forEach(c => {
        html += `<tr><td><span class="color-swatch" style="background:${c.hex}"></span> ${c.name}</td>`;
        cols.forEach(s => {
            html += `<td><input type="number" name="stock[${c.id}_${s.id}]" value="0" min="0" class="form-control form-control-sm"></td>`;
        });
        html += `</tr>`;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

document.querySelectorAll('.color-check, .size-check').forEach(el => {
    el.addEventListener('change', buildMatrix);
});
buildMatrix();
</script>
@endpush
