@extends('layouts.admin')
@section('title', 'Sửa: ' . $product->name)
@section('page-title', 'Sửa Sản phẩm')

@push('styles')
<style>
    .variant-matrix th, .variant-matrix td { text-align:center; font-size:.82rem; padding:.4rem .5rem; }
    .variant-matrix input[type=number] { width:70px; text-align:center; }
    .color-swatch { width:18px;height:18px;border-radius:50%;border:2px solid #ddd;display:inline-block;vertical-align:middle; }
    .section-title { font-weight:700; font-size:.9rem; color:#374151; border-left:3px solid #c8a96e; padding-left:.75rem; margin-bottom:1rem; }
    .img-thumb { width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb; }
    .img-thumb.is-main { border-color:#c8a96e; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i></a>
    <div><h5 class="mb-0 fw-700">Sửa: {{ $product->name }}</h5></div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-3">
    <strong>Vui lòng kiểm tra lại:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-4">

    {{-- LEFT --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Thông tin cơ bản</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                               value="{{ old('sku', $product->sku) }}">
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả ngắn</label>
                        <input type="text" name="short_description" class="form-control"
                               value="{{ old('short_description', $product->short_description) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Chất liệu</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material', $product->material) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fit</label>
                        <input type="text" name="fit" class="form-control" value="{{ old('fit', $product->fit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thương hiệu</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Giá bán</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Giá gốc <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giá sale</label>
                        <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bắt đầu sale</label>
                        <input type="datetime-local" name="sale_start" class="form-control"
                               value="{{ old('sale_start', $product->sale_start?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kết thúc sale</label>
                        <input type="datetime-local" name="sale_end" class="form-control"
                               value="{{ old('sale_end', $product->sale_end?->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Ảnh hiện tại --}}
        @if($product->images->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Ảnh hiện tại ({{ $product->images->count() }})</span>
                <small class="text-muted">Bấm sao để chọn ảnh chính, thùng rác để xóa</small>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    @foreach($product->images as $image)
                    <div class="position-relative d-inline-block text-center p-1 border rounded bg-white shadow-sm" style="width:96px">
                        <img src="{{ $image->url }}"
                             onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'"
                             class="img-thumb w-100 {{ $image->is_main ? 'is-main' : '' }}"
                             style="height:80px;object-fit:cover"
                             title="{{ $image->is_main ? 'Ảnh chính' : '' }}">
                        @if($image->is_main)
                        <span class="position-absolute top-0 start-0 badge" style="background:#c8a96e;font-size:.65rem;margin:5px">Chính</span>
                        @endif
                        <div class="mt-1 d-flex justify-content-center gap-1">
                            @if(!$image->is_main)
                            <button type="submit" form="form-main-{{ $image->id }}" class="btn btn-sm btn-outline-secondary p-1" title="Đặt làm ảnh chính" style="line-height:1;font-size:.72rem">
                                <i class="fa-solid fa-star text-warning"></i>
                            </button>
                            @endif
                            <button type="submit" form="form-del-{{ $image->id }}" class="btn btn-sm btn-outline-danger p-1" title="Xóa ảnh này" style="line-height:1;font-size:.72rem" onclick="return confirm('Bạn có chắc muốn xóa ảnh này?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">Thêm ảnh mới</div>
            <div class="card-body">
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <div class="form-text">jpg, jpeg, png, webp – tối đa 3MB mỗi ảnh.</div>
            </div>
        </div>

        {{-- Variants --}}
        <div class="card mb-4">
            <div class="card-header">Biến thể & Tồn kho</div>
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
                                       {{ $selectedColorIds->contains($color->id) ? 'checked' : '' }}>
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
                                       {{ $selectedSizeIds->contains($size->id) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="size_{{ $size->id }}">{{ $size->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="variantMatrix"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Trạng thái</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        @foreach(['active' => 'Đang bán', 'inactive' => 'Ẩn', 'draft' => 'Nháp'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $product->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured"
                           {{ old('featured', $product->featured) ? 'checked' : '' }}>
                    <label class="form-check-label" for="featured">
                        <i class="fa-solid fa-star text-warning me-1"></i>Sản phẩm nổi bật
                    </label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-accent w-100 py-2">
            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
        </button>
    </div>
</div>
</form>

@foreach($product->images as $image)
<form id="form-main-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.set-main', [$product, $image]) }}" class="d-none">
    @csrf @method('PATCH')
</form>
<form id="form-del-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" class="d-none">
    @csrf @method('DELETE')
</form>
@endforeach
@endsection

@push('scripts')
<script>
const colorData   = @json($colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex_code]));
const sizeData    = @json($sizes->map(fn($s)  => ['id' => $s->id, 'name' => $s->name]));
// Tồn kho hiện tại: {'colorId_sizeId': stock}
const existingStock = @json($product->variants->mapWithKeys(fn($v) => ["{$v->color_id}_{$v->size_id}" => $v->stock]));

function buildMatrix() {
    const selColors = [...document.querySelectorAll('.color-check:checked')].map(e => parseInt(e.value));
    const selSizes  = [...document.querySelectorAll('.size-check:checked')].map(e => parseInt(e.value));
    const el = document.getElementById('variantMatrix');
    if (!selColors.length || !selSizes.length) { el.innerHTML = ''; return; }

    const cols = sizeData.filter(s => selSizes.includes(s.id));
    const rows = colorData.filter(c => selColors.includes(c.id));

    let html = `<div class="table-responsive"><table class="table table-bordered variant-matrix">
    <thead class="table-light"><tr><th>Màu \\ Size</th>`;
    cols.forEach(s => html += `<th>${s.name}</th>`);
    html += `</tr></thead><tbody>`;
    rows.forEach(c => {
        html += `<tr><td><span class="color-swatch" style="background:${c.hex}"></span> ${c.name}</td>`;
        cols.forEach(s => {
            const val = existingStock[`${c.id}_${s.id}`] ?? 0;
            html += `<td><input type="number" name="stock[${c.id}_${s.id}]" value="${val}" min="0" class="form-control form-control-sm"></td>`;
        });
        html += `</tr>`;
    });
    html += `</tbody></table></div>`;
    el.innerHTML = html;
}

document.querySelectorAll('.color-check, .size-check').forEach(el => el.addEventListener('change', buildMatrix));
buildMatrix();
</script>
@endpush
